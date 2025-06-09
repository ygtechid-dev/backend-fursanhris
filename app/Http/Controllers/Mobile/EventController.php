<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    // public function index()
    // {
    //     if (Auth::user()->can('Manage Event')) {
    //         $user = User::with('employee')
    //             ->where('id', Auth::user()->id)->first();

    //         // Get events where the employee is assigned via the pivot table
    //         $events = Event::whereHas('employees', function ($query) use ($user) {
    //             $query->where('event_employees.user_id', $user->employee->id);
    //         })->get();

    //         $today_date = date('m');
    //         // Get current month events where the employee is assigned
    //         $current_month_event = Event::whereHas('employees', function ($query) use ($user) {
    //             $query->where('event_employees.user_id', $user->employee->id);
    //         })
    //             ->select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')
    //             ->whereNotNull(['start_date', 'end_date'])
    //             ->whereMonth('start_date', $today_date)
    //             ->whereMonth('end_date', $today_date)
    //             ->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Event retrieved successfully',
    //             'data' => [
    //                 // 'arrEvents' => $arrEvents,
    //                 'events' => $events,
    //                 'current_month_event' => $current_month_event,
    //             ]
    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Permission denied.',
    //         ], 403);
    //     }
    // }

    public function index()
    {
        if (!Auth::user()->can('Manage Event')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $currentUser = Auth::user();

            // Load user with employee relationship
            $user = User::with('employee')->find($currentUser->id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Check if employee relationship exists
            if (!$user->employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee profile not found for this user.',
                ], 404);
            }

            $employeeId = $user->employee->id;
            $userId = $user->id;

            // Initialize collection
            $calendarItems = collect();

            // Retrieve Events with error handling
            try {

                $events = Event::whereHas('employees', function ($query) use ($employeeId) {
                    $query->where('event_employees.user_id', $employeeId);
                })
                    ->select('id', 'title', 'start_date', 'end_date', 'color', 'description', 'created_by')
                    ->get()
                    ->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'title' => $event->title,
                            'start' => $event->start_date,
                            'end' => $event->end_date,
                            'color' => $event->color ?? '#3788d8',
                            'type' => 'event',
                            'status' => $event->status ?? null,
                            'priority' => $event->priority ?? null,
                            'project_id' => $event->project_id ?? null,
                            'description' => $event->description,
                            'created_by' => $event->created_by
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error retrieving events: ' . $e->getMessage());
                $events = collect();
            }

            // Retrieve Tasks with error handling
            try {
                $tasks = Task::whereHas('assignees', function ($query) use ($userId) {
                    $query->where('task_assignees.user_id', $userId);
                })
                    ->select('id', 'title', 'due_date', 'status', 'priority', 'project_id', 'description', 'created_by')
                    ->get()
                    ->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'start' => $task->due_date,
                            'end' => $task->due_date,
                            'color' => $this->getTaskColor($task->status ?? 'pending'),
                            'type' => 'task',
                            'status' => $task->status,
                            'priority' => $task->priority,
                            'project_id' => $task->project_id,
                            'description' => $task->description,
                            'created_by' => $task->created_by
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error retrieving tasks: ' . $e->getMessage());
                $tasks = collect();
            }

            // Merge Events and Tasks
            $calendarItems = collect($events ?? [])->merge(collect($tasks ?? []))->sortBy('start')->values();

            return response()->json([
                'status' => true,
                'message' => 'Calendar items retrieved successfully',
                'data' => $calendarItems
            ], 200);
        } catch (\Exception $e) {
            Log::error('Calendar index error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving calendar items.',
            ], 500);
        }
    }

    // Helper method to assign colors based on task status
    private function getTaskColor($status)
    {
        switch ($status) {
            case 'todo':
                return '#FF6B6B'; // Red for todo
            case 'in_progress':
                return '#4ECDC4'; // Teal for in progress
            case 'done':
                return '#45B7D1'; // Blue for done
            default:
                return '#A8A8A8'; // Grey for undefined status
        }
    }
}
