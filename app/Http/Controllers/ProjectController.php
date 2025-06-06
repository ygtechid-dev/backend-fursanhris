<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Calculate project completion percentage based on completed tasks
     * 
     * @param Project $project
     * @return int
     */
    private function calculateProjectCompletion(Project $project)
    {
        $completedTasks = $project->tasksByStatus('done')->count();
        $totalTasks = $project->tasks->count();

        // If there are no tasks, return 0
        if ($totalTasks === 0) {
            return 0;
        }

        // Calculate completion percentage
        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Display a listing of the projects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Project')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $query = Project::query();
        $projects = $query->with(['company', 'tasks', 'members.employee'])
            ->when(Auth::user()->type != 'super admin', function ($q) use ($user) {
                $q->where('created_by', $user->creatorId());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($project) use ($companyTz) {
                // Use the completion calculation function
                $completion = $this->calculateProjectCompletion($project);

                return $this->formatProjectResponse($project, $companyTz, $completion);
            });

        // Count projects by status
        $projectCounts = [
            'active' => 0,
            'on_hold' => 0,
            'completed' => 0
        ];

        $statusCounts = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statusCounts as $status => $count) {
            $projectCounts[$status] = $count;
        }

        return response()->json([
            'status' => true,
            'message' => 'Projects retrieved successfully',
            'data' => [
                'projects' => $projects,
                'project_counts' => $projectCounts,
            ]
        ], 200);
    }

    /**
     * Display the specified project.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Project')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $project = Project::with(['tasks', 'members.employee'])->findOrFail($id);
            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
            $completion = $this->calculateProjectCompletion($project);

            return response()->json([
                'status' => true,
                'message' => 'Project retrieved successfully',
                'data' => $this->formatProjectResponse($project, $companyTz, $completion)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
            ], 404);
        }
    }

    /**
     * Store a newly created project in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Project')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,on_hold,completed',
            'members' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();

        try {
            // Create the project
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                // 'created_by' => Auth::user()->id,
                'created_by' => Auth::user()->type != 'super admin' ? Auth::user()->creatorId() : $request->created_by,
            ]);

            // Attach members if provided
            if ($request->has('members') && is_array($request->members)) {
                $memberData = [];
                foreach ($request->members as $userId) {
                    $memberData[$userId['id']] = [
                        'assigned_by' => Auth::user()->id
                    ];
                }
                $project->members()->attach($memberData);
            }

            DB::commit();

            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            // Refresh project with relationships
            $project = Project::with(['tasks', 'members.employee'])->find($project->id);

            return response()->json([
                'status' => true,
                'message' => 'Project created successfully',
                'data' => $this->formatProjectResponse($project, $companyTz)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified project in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Project')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'status' => 'sometimes|required|in:planning,active,on_hold,completed',
            'members' => 'nullable|array',
            // 'members.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->messages()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Update project fields if provided
            if ($request->has('name')) {
                $project->name = $request->name;
            }
            if ($request->has('description')) {
                $project->description = $request->description;
            }
            if ($request->has('start_date')) {
                $project->start_date = $request->start_date;
            }
            if ($request->has('end_date')) {
                $project->end_date = $request->end_date;
            }
            if ($request->has('status')) {
                $project->status = $request->status;
            }
            if ($request->has('created_by')) {
                $project->created_by =  Auth::user()->type != 'super admin' ? Auth::user()->creatorId() : $request->created_by;
            }

            $project->save();

            // Update members if provided
            if ($request->has('members')) {

                $memberData = [];
                foreach ($request->members as $userId) {
                    $memberData[$userId['id']] = [
                        'assigned_by' => Auth::user()->id
                    ];
                }
                // Sync members (removes existing and adds new)
                $project->members()->sync($memberData);
            }

            DB::commit();

            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            // Refresh project with relationships
            $project = Project::with(['tasks', 'members.employee'])->find($project->id);
            $completion = $this->calculateProjectCompletion($project);

            return response()->json([
                'status' => true,
                'message' => 'Project updated successfully',
                'data' => $this->formatProjectResponse($project, $companyTz, $completion)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified project from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Project')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Soft delete the project (using SoftDeletes trait)
            $project->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Project deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format project data for response
     *
     * @param Project $project
     * @param string $timezone
     * @param int $completion
     * @return array
     */
    private function formatProjectResponse($project, $timezone = 'UTC', $completion = null)
    {
        // If completion is not provided, calculate it
        if ($completion === null) {
            $completion = $this->calculateProjectCompletion($project);
        }

        // Get completed and total task counts for reference
        $completedTasks = $project->tasksByStatus('done')->count();
        $totalTasks = $project->tasks->count();

        // Format member data
        $members = $project->members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->employee_name(),
                'email' => $member->email,
                'avatar' => $member->avatar,
                'type' => $member->type,
                'employee' => $member?->employee, // Include employee relationship data if needed
                'assigned_by' => $member->pivot->assigned_by,
                'assigned_at' => $member->pivot->created_at,
            ];
        });

        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'status' => $project->status,
            'progress' => $completion,
            'members_count' => $project->members->count(),
            'members' => $members,
            'company' => $project?->company ?? null,
            'tasks_count' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'created_at' => Carbon::parse($project->created_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($project->updated_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'created_by' => $project?->created_by,
        ];
    }
}
