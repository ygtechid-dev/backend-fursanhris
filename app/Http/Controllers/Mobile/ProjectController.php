<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
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

        // if (!Auth::user()->can('Manage All Projects')) {
        $query->whereHas('members', function ($q) use ($user) {
            $q->where('project_members.user_id', $user->id);
        });
        // }

        $projects = $query->with(['tasks'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($project) use ($companyTz) {
                return $this->formatProjectResponse($project, $companyTz);
            });


        // Count projects by status for the last month
        // $startOfMonth = Carbon::now()->startOfMonth();
        // $endOfMonth = Carbon::now()->endOfMonth();

        $projectCounts = [
            'active' => 0,
            'on_hold' => 0,
            'completed' => 0
        ];

        // if (!Auth::user()->can('Manage All Projects')) {
        $statusCounts = Project::whereHas('members', function ($q) use ($user) {
            $q->where('project_members.user_id', $user->id);
        })
            // ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // } else {
        //     $statusCounts = Project::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        //         ->select('status', DB::raw('count(*) as count'))
        //         ->groupBy('status')
        //         ->pluck('count', 'status')
        //         ->toArray();
        // }

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
     * @return array
     */
    private function formatProjectResponse($project, $timezone)
    {
        $completedTasks = $project->tasksByStatus('completed')->count();
        $totalTasks = $project->tasks->count();
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'status' => $project->status,
            'progress' => $progress,
            'members_count' => $project->members->count(),
            'tasks_count' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'created_at' => Carbon::parse($project->created_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($project->updated_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
        ];
    }
}
