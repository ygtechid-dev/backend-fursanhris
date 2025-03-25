<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of all tasks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $query = Task::with(['project', 'assignees', 'attachments', 'comments']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->status($request->status);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by project if provided
        if ($request->has('project_id') || !empty($id)) {
            $pId = $request->has('project_id') ? $request->has('project_id') : $id;
            $query->where('project_id', $pId);
        }

        // Filter by assigned user if provided
        if ($request->has('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        // Filter by due date range if provided
        if ($request->has('due_date_from') && $request->has('due_date_to')) {
            $query->whereBetween('due_date', [$request->due_date_from, $request->due_date_to]);
        }

        // Sort by due date, priority, or creation date
        // if ($request->has('sort_by')) {
        //     $sortDirection = $request->input('sort_direction', 'asc');
        //     $query->orderBy($request->sort_by, $sortDirection);
        // } else {
        //     $query->orderBy('created_at', 'desc'); // Default sorting
        // }

        // $tasks = $query->paginate($request->input('per_page', 15));
        $tasks = $query
            ->orderBy('position')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Task retrieved successfully',
            'data' => $tasks,
        ]);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
            'status' => 'required|string|in:todo,in_progress,in_review,done',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned' => 'nullable|array',
            'assigned.*.id' => 'exists:users,id',
        ]);
        // Begin transaction
        DB::beginTransaction();

        try {
            // Get the maximum position in the status column
            $maxPosition = Task::where('status', $request->status)
                ->where('project_id', $request->project_id)
                ->max('position');

            // Create new task
            $task = Task::create([
                'title' => $request->title,
                'project_id' => $request->project_id,
                'description' => $request->description,
                'status' => $request->status,
                'priority' => $request->priority ?? 'medium',
                'due_date' => $request->due_date,
                'position' => $maxPosition ? $maxPosition + 1 : 1,
                'created_by'   => Auth::user()->creatorId()
            ]);

            // Assign users if provided
            if ($request->has('assigned')) {
                // Get user IDs from the request
                $userIds = collect($request->assigned)->pluck('id')->toArray();

                // Prepare an array to store attachments with assigned_by
                $attachments = [];
                foreach ($userIds as $userId) {
                    $attachments[$userId] = [
                        'assigned_by' => Auth::user()->id
                    ];
                }

                // Attach the assignees with additional pivot data
                $task->assignees()->attach($attachments);
            }

            DB::commit();

            // Load relationships for the response
            $task->load(['project', 'assignees', 'attachments', 'comments']);

            return response()->json([
                'status' => true,
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified task.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Find task with related data
            $task = Task::with(['project', 'assignees', 'attachments', 'comments'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Task details retrieved successfully',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve task details',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:todo,in_progress,in_review,done',
            'priority' => 'required|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned' => 'nullable|array',
            'assigned.*.id' => 'exists:users,id',
        ]);

        // Find the task
        $task = Task::findOrFail($id);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Update task fields
            $task->update([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'priority' => $request->priority,
                'due_date' => $request->due_date,
            ]);

            // Update assignees if provided
            if ($request->has('assigned')) {
                // Get user IDs from the request
                $userIds = collect($request->assigned)->pluck('id')->toArray();

                // Prepare an array to store attachments with assigned_by
                $assignees = [];
                foreach ($userIds as $userId) {
                    $assignees[$userId] = [
                        'assigned_by' => Auth::user()->id
                    ];
                }

                // Sync the assignees
                $task->assignees()->sync($assignees);
            }

            // dd($request->all(), $task->wasChanged('status'));
            // If the status has changed, update the position
            if ($task->wasChanged('status')) {
                // Get the maximum position in the new status column
                $maxPosition = Task::where('status', $task->status)
                    ->where('project_id', $task->project_id)
                    ->max('position');

                // Set the task position to be at the end of the column
                $task->position = $maxPosition ? $maxPosition + 1 : 1;
                $task->save();
            }

            DB::commit();

            // Load relationships for the response
            $task->load(['assignees', 'attachments']);

            return response()->json([
                'message' => 'Task updated successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder tasks in a column
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'columnId' => 'required|integer',
            'taskIds' => 'required|array',
            'taskIds.*' => 'integer'
        ]);

        $columnId = $request->columnId;
        $taskIds = $request->taskIds;

        try {
            DB::beginTransaction();

            // Update status berdasarkan columnId
            // Mapping columnId ke status
            $statusMap = [
                1 => 'todo',
                2 => 'in_progress',
                3 => 'in_review',
                4 => 'done',
                // Tambahkan mapping lain sesuai kebutuhan
            ];

            // Pastikan columnId ada dalam mapping
            if (!isset($statusMap[$columnId])) {
                return response()->json(['error' => 'Invalid column ID'], 400);
            }

            $status = $statusMap[$columnId];

            foreach ($taskIds as $index => $taskId) {
                Task::where('id', $taskId)
                    ->update([
                        'status' => $status,
                        'position' => $index
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tasks reordered successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task status when moved between columns
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'newStatus' => 'required|in:todo,in_progress,done'
        ]);

        try {
            $task = Task::findOrFail($request->taskId);
            $task->status = $request->newStatus;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified task from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the task
            $task = Task::findOrFail($id);

            // Begin transaction
            DB::beginTransaction();

            // Delete related records (assuming you have these relationships)
            // Remove assignees
            $task->assignees()->detach();

            // Delete attachments
            foreach ($task->attachments as $attachment) {
                // Delete file from storage if needed
                // Storage::delete($attachment->file_path);
                $attachment->delete();
            }

            // Delete comments
            $task->comments()->delete();

            // Delete the task
            $task->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a comment to a task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function addComment(Request $request, $taskId)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Create the comment
            $comment = $task->comments()->create([
                'comment' => $request->comment,
                'commented_by' => Auth::id()
            ]);

            // Load user information
            $comment->load('commenter');

            return response()->json([
                'status' => true,
                'message' => 'Comment added successfully',
                'data' => $comment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a comment.
     *
     * @param  int  $taskId
     * @param  int  $commentId
     * @return \Illuminate\Http\Response
     */
    public function deleteComment($taskId, $commentId)
    {
        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Find the comment and ensure it belongs to the task
            $comment = $task->comments()->findOrFail($commentId);

            // Check if the user is authorized to delete this comment
            // You can add your authorization logic here if needed
            // For example, only allow the comment author or task owner to delete
            // if (Auth::id() !== $comment->commented_by && Auth::id() !== $task->created_by) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized to delete this comment'
            //     ], 403);
            // }

            // Delete the comment
            $comment->delete();

            return response()->json([
                'status' => true,
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all comments for a task.
     *
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function getComments($taskId)
    {
        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Get comments with user information
            $comments = $task->comments()->with('commenter')->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Comments retrieved successfully',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all attachments for a task.
     *
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function getAttachments($taskId)
    {
        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Get attachments with user information
            $attachments = $task->attachments()->with('uploader')->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Attachments retrieved successfully',
                'data' => $attachments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve attachments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a new attachment for a task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function uploadAttachment(Request $request, $taskId)
    {
        // Validasi untuk mendukung file atau URL
        $request->validate([
            'file' => 'nullable|file|max:10240', // Optional file upload
            'url' => 'nullable|url|max:255', // Optional URL
        ]);

        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Cek apakah ada file yang diunggah
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Store the file
                $filePath = $file->storeAs('task_attachments', $fileName, 'public');

                // Get full URL for the file (using the app URL)
                $fullUrl = url('/storage/' . $filePath);

                // Create attachment record
                $attachment = $task->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $fullUrl,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => Auth::user()->id,
                    'url' => $request->url ?? null // Tambahkan URL jika disediakan
                ]);
            }
            // Cek apakah ada URL yang diberikan
            else if ($request->has('url')) {
                // Buat attachment dengan hanya URL
                $attachment = $task->attachments()->create([
                    'url' => $request->url,
                    'uploaded_by' => Auth::user()->id
                ]);
            } else {
                // Jika tidak ada file atau URL
                return response()->json([
                    'status' => false,
                    'message' => 'Either file or URL is required'
                ], 400);
            }

            // Load user information
            $attachment->load('uploader');

            return response()->json([
                'status' => true,
                'message' => 'Attachment uploaded successfully',
                'data' => $attachment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to upload attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an attachment.
     *
     * @param  int  $taskId
     * @param  int  $attachmentId
     * @return \Illuminate\Http\Response
     */
    public function deleteAttachment($taskId, $attachmentId)
    {
        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Find the attachment and ensure it belongs to the task
            $attachment = $task->attachments()->findOrFail($attachmentId);

            // Extract the storage path from the full URL
            $urlPath = parse_url($attachment->file_path, PHP_URL_PATH);
            $storagePath = str_replace('/storage/', '', $urlPath);

            // Delete the file from storage
            Storage::disk('public')->delete($storagePath);

            // Delete the attachment record
            $attachment->delete();

            return response()->json([
                'status' => true,
                'message' => 'Attachment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
