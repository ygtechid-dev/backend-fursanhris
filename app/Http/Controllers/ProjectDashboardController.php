<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDashboardController extends Controller
{
    /**
     * Show project by project ic
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProject(Request $request)
    {
        // Get current user
        $user = Auth::user();
        // $companyId = $user->creatorId();
        $projectId = $request->query('projectId');

        $project = Project::with('members')->find($projectId);

        // Return all statistics
        return response()->json($project);
    }

    /**
     * Get project management dashboard statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        // Get current user
        $user = Auth::user();
        $companyId = $user->creatorId();
        $projectId = $request->query('projectId');

        // Get projects statistics
        $projectsStats = $this->getProjectStats($companyId, $projectId);

        // Get tasks statistics
        $tasksStats = $this->getTaskStats($companyId, $projectId);

        // Get priority statistics
        $priorityStats = $this->getPriorityStats($companyId, $projectId);

        // Get upcoming deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines($companyId, $projectId);

        // Return all statistics
        return response()->json([
            'projects' => $projectsStats,
            'tasks' => $tasksStats,
            'priorities' => $priorityStats,
            'upcomingDeadlines' => $upcomingDeadlines
        ]);
    }

    /**
     * Get project completion percentages
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectCompletion(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        $projectId = $request->query('projectId');

        $projectsQuery = Project::where('created_by', $companyId)
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'done');
            }]);

        // Filter by project ID if provided
        if ($projectId) {
            $projectsQuery->where('id', $projectId);
        }

        $projects = $projectsQuery->get();

        $data = $projects->map(function ($project) {
            // Calculate completion percentage
            $completion = 0;
            if ($project->tasks_count > 0) {
                $completion = round(($project->completed_tasks_count / $project->tasks_count) * 100);
            }

            return [
                'name' => $project->name,
                'completion' => $completion
            ];
        });

        return response()->json($data);
    }

    /**
     * Get recent activities on projects and tasks
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentActivities(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        $limit = $request->get('limit', 5);
        $projectId = $request->query('projectId');

        // Get latest task comments
        $taskCommentsQuery = TaskComment::with(['task', 'commenter'])
            ->whereHas('task', function ($query) use ($companyId, $projectId) {
                $query->whereHas('project', function ($q) use ($companyId, $projectId) {
                    $q->where('created_by', $companyId);
                    if ($projectId) {
                        $q->where('id', $projectId);
                    }
                });
            })
            ->latest()
            ->take($limit);

        $taskCommentsArray = $taskCommentsQuery->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'type' => 'task',
                    'action' => 'commented',
                    'name' => $comment->task->title,
                    'user' => $comment->commenter->employee_name(),
                    'time' => $this->getTimeAgo($comment->created_at)
                ];
            })->toArray();  // Convert to array

        // Get latest updated tasks
        $updatedTasksQuery = Task::with(['project', 'assignees'])
            ->whereHas('project', function ($query) use ($companyId, $projectId) {
                $query->where('created_by', $companyId);
                if ($projectId) {
                    $query->where('id', $projectId);
                }
            })
            ->latest('updated_at')
            ->take($limit);

        $updatedTasksArray = $updatedTasksQuery->get()
            ->map(function ($task) {
                $action = 'created';
                if ($task->created_at != $task->updated_at) {
                    if ($task->status == 'done') {
                        $action = 'completed';
                    } else {
                        $action = 'updated';
                    }
                }

                return [
                    'id' => $task->id,
                    'type' => 'task',
                    'action' => $action,
                    'name' => $task->title,
                    'user' => $task->assignees->first() ? $task->assignees->first()->employee_name() : 'System',
                    'time' => $this->getTimeAgo($task->updated_at)
                ];
            })->toArray();  // Convert to array

        // Merge arrays (not Collections)
        $activities = array_merge($taskCommentsArray, $updatedTasksArray);

        // Sort the merged array
        usort($activities, function ($a, $b) {
            // Convert time strings to comparable values for sorting
            return $this->convertTimeStringToTimestamp($b['time']) - $this->convertTimeStringToTimestamp($a['time']);
        });

        // Take only requested number of items
        $activities = array_slice($activities, 0, $limit);

        return response()->json($activities);
    }

    /**
     * Convert "time ago" string to timestamp for sorting
     *
     * @param string $timeString
     * @return int
     */
    private function convertTimeStringToTimestamp($timeString)
    {
        $now = Carbon::now();

        if (strpos($timeString, 'just now') !== false) {
            return $now->timestamp;
        } elseif (preg_match('/(\d+) (\w+) ago/', $timeString, $matches)) {
            $value = $matches[1];
            $unit = $matches[2];
            // Handle singular/plural conversion
            if (substr($unit, -1) === 's' && $value == 1) {
                $unit = substr($unit, 0, -1);
            }
            return $now->sub($unit, $value)->timestamp;
        } elseif (strpos($timeString, 'Yesterday') !== false) {
            return Carbon::yesterday()->timestamp;
        } else {
            // For dates in format "M d, Y"
            return Carbon::parse($timeString)->timestamp;
        }
    }

    /**
     * Get project statistics
     *
     * @param int $companyId
     * @param int|null $projectId
     * @return array
     */
    private function getProjectStats($companyId, $projectId = null)
    {
        // If project ID is specified, only return stats for that project
        if ($projectId) {
            $project = Project::when(Auth::user()->type != 'super admin', function ($q) use ($companyId) {
                $q->where('created_by', $companyId);
            })
                ->where('id', $projectId)
                ->first();

            if ($project) {
                return [
                    'active' => $project->status === 'active' ? 1 : 0,
                    'on_hold' => $project->status === 'on_hold' ? 1 : 0,
                    'completed' => $project->status === 'completed' ? 1 : 0,
                    'total' => 1
                ];
            } else {
                return [
                    'active' => 0,
                    'on_hold' => 0,
                    'completed' => 0,
                    'total' => 0
                ];
            }
        }

        // Otherwise, return stats for all projects
        $activeCount = Project::when(Auth::user()->type != 'super admin', function ($q) use ($companyId) {
            $q->where('created_by', $companyId);
        })
            ->where('status', 'active')
            ->count();

        $onHoldCount = Project::when(Auth::user()->type != 'super admin', function ($q) use ($companyId) {
            $q->where('created_by', $companyId);
        })
            ->where('status', 'on_hold')
            ->count();

        $completedCount = Project::when(Auth::user()->type != 'super admin', function ($q) use ($companyId) {
            $q->where('created_by', $companyId);
        })
            ->where('status', 'completed')
            ->count();

        $totalCount = Project::when(Auth::user()->type != 'super admin', function ($q) use ($companyId) {
            $q->where('created_by', $companyId);
        })->count();

        return [
            'active' => $activeCount,
            'on_hold' => $onHoldCount,
            'completed' => $completedCount,
            'total' => $totalCount
        ];
    }

    /**
     * Get task statistics
     *
     * @param int $companyId
     * @param int|null $projectId
     * @return array
     */
    private function getTaskStats($companyId, $projectId = null)
    {
        $query = Task::whereHas('project', function ($query) use ($companyId) {
            if (Auth::user()->type == 'company') {
                $query->where('created_by', $companyId);
            }
        });

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $todoCount = (clone $query)->where('status', 'todo')->count();
        $inProgressCount = (clone $query)->where('status', 'in_progress')->count();
        $inReviewCount = (clone $query)->where('status', 'in_review')->count();
        $doneCount = (clone $query)->where('status', 'done')->count();
        $totalCount = (clone $query)->count();

        return [
            'todo' => $todoCount,
            'in_progress' => $inProgressCount,
            'in_review' => $inReviewCount,
            'done' => $doneCount,
            'total' => $totalCount
        ];
    }

    /**
     * Get priority statistics
     *
     * @param int $companyId
     * @param int|null $projectId
     * @return array
     */
    private function getPriorityStats($companyId, $projectId = null)
    {
        $query = Task::whereHas('project', function ($query) use ($companyId) {
            if (Auth::user()->type == 'company') {
                $query->where('created_by', $companyId);
            }
        });

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $highCount = (clone $query)->where('priority', 'high')->count();
        $mediumCount = (clone $query)->where('priority', 'medium')->count();
        $lowCount = (clone $query)->where('priority', 'low')->count();

        return [
            'high' => $highCount,
            'medium' => $mediumCount,
            'low' => $lowCount
        ];
    }

    /**
     * Get upcoming deadlines
     *
     * @param int $companyId
     * @param int|null $projectId
     * @return array
     */
    private function getUpcomingDeadlines($companyId, $projectId = null)
    {
        $today = Carbon::today();
        $twoWeeksFromNow = Carbon::today()->addDays(14);

        $query = Task::with('project')
            ->whereHas('project', function ($query) use ($companyId) {
                if (Auth::user()->type == 'company') {
                    $query->where('created_by', $companyId);
                }
            })
            ->whereBetween('due_date', [$today, $twoWeeksFromNow])
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->limit(5);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->title,
                    'project' => $task->project->name,
                    'dueDate' => $task->due_date
                ];
            });
    }

    /**
     * Convert timestamp to "time ago" format
     *
     * @param \Carbon\Carbon $timestamp
     * @return string
     */
    private function getTimeAgo($timestamp)
    {
        $now = Carbon::now();
        $diff = $timestamp->diffInSeconds($now);

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 172800) {
            return 'Yesterday';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $timestamp->format('M d, Y');
        }
    }
}
