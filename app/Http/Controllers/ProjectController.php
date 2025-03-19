<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function index()
    {
        if (!Auth::user()->can('Manage Project')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get projects based on role
        // If user has admin role or can manage all projects, show all projects
        // Otherwise show only projects where the employee is a member
        $query = Project::query();
        $projects = $query->with(['tasks', 'members.employee'])
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
     * Format project data for response
     *
     * @param Project $project
     * @param string $timezone
     * @param int $completion
     * @return array
     */
    private function formatProjectResponse($project, $timezone, $completion = null)
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
            'members' => $members, // Include the formatted members data
            'tasks_count' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'created_at' => Carbon::parse($project->created_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($project->updated_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
        ];
    }
}
