<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    /**
     * Get all tasks assigned to the authenticated employee across all projects
     */
    public function getAssignedTasks()
    {
        if (!Auth::user()->can('Manage Task')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get all tasks assigned to the employee
        $tasks = Task::with(['project', 'assignees', 'attachments', 'comments'])
            ->assignedTo($user->id)
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($task) use ($companyTz) {
                return $this->formatTaskResponse($task, $companyTz);
            });

        // Count tasks by status
        $taskCounts = [
            'todo' => 0,
            'in_progress' => 0,
            'done' => 0
        ];

        $statusCounts = Task::assignedTo($user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statusCounts as $status => $count) {
            $taskCounts[$status] = $count;
        }

        return response()->json([
            'status' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => [
                'tasks' => $tasks,
                'task_counts' => $taskCounts,
            ]
        ], 200);
    }

    /**
     * Create a task for the authenticated employee themselves
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOwnTask(Request $request)
    {
        // Check if user has permission to create tasks
        if (!Auth::user()->can('Create Task')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        // Validate the request with more flexible attachment validation
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date|after_or_equal:today',
            'attachments' => 'nullable|array',
            'attachments.*' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    // Validate that each attachment is either a file or has a URL
                    if (!isset($value['file']) && !isset($value['url'])) {
                        $fail('Each attachment must have either a file or a URL.');
                    }

                    // If file is present, validate file
                    if (isset($value['file'])) {
                        $fileValidator = Validator::make(
                            ['file' => $value['file']],
                            ['file' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120']
                        );

                        if ($fileValidator->fails()) {
                            $fail($fileValidator->errors()->first());
                        }
                    }

                    // If URL is present, validate URL
                    if (isset($value['url'])) {
                        $urlValidator = Validator::make(
                            ['url' => $value['url']],
                            ['url' => 'url|max:255']
                        );

                        if ($urlValidator->fails()) {
                            $fail($urlValidator->errors()->first());
                        }
                    }
                }
            ],
            'assigned' => 'nullable|array',
            'assigned.*.id' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the authenticated user
        $user = User::with('employee')->where('id', Auth::user()->id)->first();

        // Begin database transaction
        DB::beginTransaction();

        try {
            // Create new task
            $task = Task::create([
                'project_id'  => $request->project_id,
                'title'       => $request->title,
                'description' => $request->description,
                'status'      => 'todo',
                'priority'    => $request->priority,
                'due_date'    => $request->due_date,
                'created_by'  => $user->creatorId(),
            ]);

            // Assign users if provided
            if ($request->has('assigned')) {
                $userIds = collect($request->assigned)->pluck('id')->toArray();
                $assignees = [];
                foreach ($userIds as $userId) {
                    $assignees[$userId] = [
                        'assigned_by' => Auth::user()->id
                    ];
                }
                $task->assignees()->attach($assignees);
            }

            // Handle attachments if any
            if ($request->has('attachments')) {
                foreach ($request->attachments as $attachment) {
                    // Handle file attachments
                    if (isset($attachment['file'])) {
                        $file = $attachment['file'];
                        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $filePath = 'task_attachments/' . $task->id . '/' . $fileName;

                        // Store the file
                        Storage::disk('public')->put($filePath, file_get_contents($file));
                        $fileUrl = asset('storage/' . $filePath);

                        // Handle URL attachments
                        // $url_from_request = null;
                        // if (isset($attachment['url'])) {
                        //     $url_from_request = $attachment['url'];
                        // }

                        TaskAttachment::create([
                            'task_id' => $task->id,
                            'file_name' => $fileName,
                            'file_path' => $fileUrl,
                            'file_size' => $file->getSize(),
                            'file_type' => $file->getMimeType(),
                            'url' => isset($attachment['url']) ? $attachment['url'] : null,
                            'uploaded_by' => Auth::user()->id,
                        ]);
                    } else if (isset($attachment['url'])) {
                        TaskAttachment::create([
                            'task_id' => $task->id,
                            'url' => $attachment['url'],
                            'uploaded_by' => Auth::user()->id,
                        ]);
                    }
                }
            }

            // Get company timezone for response formatting
            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            // Commit transaction
            DB::commit();

            // Format the task response
            $formattedTask = $this->formatTaskResponse($task->fresh(['project', 'assignees', 'attachments', 'comments']), $companyTz);

            return response()->json([
                'status' => true,
                'message' => 'Task created successfully',
                'data' => $formattedTask
            ], 201);
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of a task
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        // Check if user has permission
        if (!Auth::user()->can('Edit Task')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,done',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user's employee record
        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        // Find the task and check if the employee is assigned to it
        $task = Task::with(['assignees'])->find($taskId);

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 404);
        }

        // Check if employee is assigned to this task or created the task
        $isAssigned = $task->assignees()->where('task_assignees.user_id', $user->id)->exists();
        $isCreator = $task->created_by == Auth::user()->id;

        if (!$isAssigned && !$isCreator && !Auth::user()->can('Manage All Task')) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot update a task that is not assigned to you',
            ], 403);
        }

        try {
            // Update the task status
            $oldStatus = $task->status;
            $task->status = $request->status;
            $task->save();

            // If moving to 'done', record completion time
            // if ($request->status == 'done' && $oldStatus != 'done') {
            //     $task->completed_at = now();
            //     $task->completed_by = Auth::user()->id;
            //     $task->save();

            //     // Optional: create an activity log
            //     // ActivityLog::create([...]);
            // }

            // Get company timezone for response formatting
            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            // Format the task response
            $formattedTask = $this->formatTaskResponse($task->fresh(['project', 'assignees', 'attachments', 'comments']), $companyTz);

            return response()->json([
                'status' => true,
                'message' => 'Task status updated successfully',
                'data' => $formattedTask
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update task status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a comment to a task
     */
    public function addComment(Request $request, $taskId)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $task = Task::findOrFail($taskId);

        // Check if the authenticated user is assigned to this task
        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        $isAssigned = $task->assignees()->where('task_assignees.user_id', $user->id)->exists();
        $isCreator = $task->created_by == Auth::user()->id;
        $canManageAllTasks = Auth::user()->can('Manage All Task');

        if (!$isAssigned && !$isCreator && !$canManageAllTasks) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to comment on this task.',
            ], 403);
        }

        $comment = new TaskComment();
        $comment->task_id = $taskId;
        $comment->comment = $request->comment;
        $comment->commented_by = Auth::user()->id; // Using commented_by from model
        $comment->save();

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Comment added successfully',
            'data' => [
                'comment' => [
                    'id' => $comment->id,
                    'task_id' => $comment->task_id,
                    'user_id' => Auth::user()->id,
                    'user_name' => $user->name,
                    'comment' => $comment->comment,
                    'created_at' => Carbon::parse($comment->created_at)->setTimezone($companyTz)->format('Y-m-d H:i:s'),
                ]
            ]
        ], 201);
    }

    /**
     * Add an attachment to a task
     */
    public function addAttachment(Request $request, $taskId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $task = Task::findOrFail($taskId);

        // Check if the authenticated user is assigned to this task
        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        $isAssigned = $task->assignees()->where('task_assignees.user_id', $user->id)->exists();
        $isCreator = $task->created_by == Auth::user()->id;
        $canManageAllTasks = Auth::user()->can('Manage All Task');

        if (!$isAssigned && !$isCreator && !$canManageAllTasks) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to add attachments to this task.',
            ], 403);
        }

        // Upload file
        $file = $request->file('file');
        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $filePath = 'tasks/' . $taskId . '/' . $fileName;

        // Store the file
        Storage::disk('public')->put($filePath, file_get_contents($file));

        $fileUrl = asset('storage/' . $filePath);

        // Create attachment record
        $attachment = new TaskAttachment();
        $attachment->task_id = $taskId;
        $attachment->uploaded_by = Auth::user()->id; // Using uploaded_by from model
        $attachment->file_name = $fileName;
        $attachment->file_path = $fileUrl;
        $attachment->file_size = $file->getSize();
        $attachment->file_type = $file->getMimeType();

        $attachment->save();

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Attachment added successfully',
            'data' => [
                'attachment' => [
                    'id' => $attachment->id,
                    'task_id' => $attachment->task_id,
                    'user_id' => Auth::user()->id,
                    'user_name' => $user->name,
                    'file_name' => $attachment->file_name,
                    'file_path' => $attachment->file_path,
                    'file_size' => $attachment->file_size,
                    'file_type' => $attachment->file_type,
                    'description' => $attachment->description ?? null,
                    'created_at' => Carbon::parse($attachment->created_at)->setTimezone($companyTz)->format('Y-m-d H:i:s'),
                ]
            ]
        ], 201);
    }

    /**
     * Get detailed information about a specific task
     * 
     * @param int $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaskDetail($taskId)
    {
        // Check if user has permission to view task details
        if (!Auth::user()->can('Manage Task')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        // Get user's employee record
        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        // Find the task with all related data
        $task = Task::with([
            'project',
            'assignees',
            'attachments.uploader',
            'comments.commenter'
        ])->find($taskId);

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 404);
        }

        // Check if employee is assigned to this task, created the task, or has manage all tasks permission
        $isAssigned = $task->assignees()->where('task_assignees.user_id', $user->id)->exists();
        $isCreator = $task->created_by == Auth::user()->id;
        $canManageAllTasks = Auth::user()->can('Manage All Task');

        // if (!$isAssigned && !$isCreator && !$canManageAllTasks) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You are not authorized to view this task',
        //     ], 403);
        // }

        // Get company timezone for response formatting
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get creator details
        // $creator = User::find($task->created_by);

        // Format the detailed task response
        $formattedTask = [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project_name' => $task->project->name,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => Carbon::parse($task->due_date)->setTimezone($companyTz)->format('Y-m-d'),
            'is_overdue' => Carbon::parse($task->due_date)->isPast() && $task->status !== 'done',
            // 'created_by' => [
            //     'id' => $creator->id,
            //     'name' => $creator->name,
            //     'avatar' => $creator->avatar ? Storage::url($creator->avatar) : null,
            // ],
            'created_at' => Carbon::parse($task->created_at)->setTimezone($companyTz)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($task->updated_at)->setTimezone($companyTz)->format('Y-m-d H:i:s'),
            'assignees' => $task->assignees->map(function ($assignee) {
                return [
                    'id' => $assignee->id,
                    'name' => $assignee->employee_name(),
                    'designation' => isset($assignee?->employee) ? $assignee?->employee?->designation?->name : ucfirst($assignee->type),
                    'avatar' => !empty($assignee->user->avatar) ? Storage::url($assignee->user->avatar) : null,
                ];
            }),
            'attachments' => $task->attachments->map(function ($attachment) use ($companyTz) {
                $uploader = $attachment->uploader;
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_path' => $attachment->file_path,
                    'file_size' => $this->formatFileSize($attachment->file_size),
                    'file_type' => $attachment->file_type,
                    'url' => $attachment->url,
                    'uploaded_by' => [
                        'id' => $uploader->id,
                        'name' => $uploader->employee_name(),
                    ],
                    'uploaded_at' => Carbon::parse($attachment->created_at)->setTimezone($companyTz)->format('Y-m-d H:i:s'),
                ];
            }),
            'comments' => $task->comments->map(function ($comment) use ($companyTz) {
                $commenter = $comment->commenter;

                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'commented_by' => [
                        'id' => $commenter->id,
                        'name' => $commenter->employee_name(),
                        'avatar' => !empty($commenter->avatar) ? Storage::url($commenter->avatar) : null,
                    ],
                    'commented_at' => Carbon::parse($comment->created_at)->setTimezone($companyTz)->format('Y-m-d H:i:s'),
                ];
            })->sortByDesc('commented_at')->values(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Task details retrieved successfully',
            'data' => $formattedTask
        ], 200);
    }

    /**
     * Format file size to human-readable format
     * 
     * @param int $bytes
     * @return string
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getEmployee()
    {
        $companyId = Auth::user()->creatorId(); // Company ID yang ingin Anda gunakan

        // Dapatkan company user
        // $company = User::where('id', $companyId)
        //     ->where('type', 'company')
        //     ->first();

        // Dapatkan semua users yang dibuat oleh company tersebut
        $employeeUsers = User::where('created_by', $companyId)
            ->where('id', '!=', $companyId) // Exclude the company itself
            ->where('type', '!=', 'company admin') // Exclude the company itself
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->employee_name(),
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'type' => $user->type,
                    'employee' => $user->type == 'employee' ? $user->employee : null,
                ];
            });


        return response()->json([
            'message' => 'Successfully retrieved data',
            'data' => $employeeUsers
        ]);
    }

    /**
     * Format task data for response
     */
    private function formatTaskResponse($task, $timezone)
    {
        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project_name' => $task->project->name,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date,
            'assignees' => $task->assignees->map(function ($assignee) {
                return [
                    'id' => $assignee->id,
                    'name' => $assignee->employee_name(),
                    'designation' => isset($assignee?->employee) ? $assignee?->employee?->designation?->name : ucfirst($assignee->type),
                    'avatar' => !empty($assignee->user->avatar) ? Storage::url($assignee->user->avatar) : null,
                ];
            }),
            'attachments_count' => $task->attachments->count(),
            'comments_count' => $task->comments->count(),
            'created_by' => $task->created_by,
            'created_at' => Carbon::parse($task->created_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($task->updated_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
        ];
    }
}
