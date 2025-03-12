<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Event')) {
            $user = User::with('employee')
                ->where('id', Auth::user()->id)->first();

            // Get events where the employee is assigned via the pivot table
            $events = Event::whereHas('employees', function ($query) use ($user) {
                $query->where('event_employees.employee_id', $user->employee->id);
            })->get();

            $today_date = date('m');
            // Get current month events where the employee is assigned
            $current_month_event = Event::whereHas('employees', function ($query) use ($user) {
                $query->where('event_employees.employee_id', $user->employee->id);
            })
                ->select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')
                ->whereNotNull(['start_date', 'end_date'])
                ->whereMonth('start_date', $today_date)
                ->whereMonth('end_date', $today_date)
                ->get();

            // $arrEvents = [];
            // foreach ($events as $event) {
            //     $arr['id']    = $event['id'];
            //     $arr['title'] = $event['title'];
            //     $arr['start_date'] = $event['start_date'];
            //     $arr['end_date']   = $event['end_date'];
            //     $arr['color'] = $event['color'];
            //     $arrEvents[] = $arr;
            // }

            return response()->json([
                'status' => true,
                'message' => 'Event retrieved successfully',
                'data' => [
                    // 'arrEvents' => $arrEvents,
                    'events' => $events,
                    'current_month_event' => $current_month_event,
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
