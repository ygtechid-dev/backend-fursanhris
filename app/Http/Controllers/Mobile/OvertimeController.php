<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OvertimeController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Overtime')) {
            $user     = User::with('employee')->where('id', Auth::user()->id)->first();
            $overtimes = Overtime::where('employee_id', '=', $user?->employee->id)->get();

            return response()->json([
                'status' => true,
                'message' => 'Overtimes retrieved successfully',
                'data' => $overtimes
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Overtime')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'employee_id' => 'nullable',
                    'title' => 'nullable',
                    'number_of_days' => 'nullable',
                    'overtime_date' => 'required|date',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i',
                    'hours' => 'nullable',
                    'rate' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->getMessageBag()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            if (!$user || !$user->employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Calculate hours between start and end time
            $startTime = Carbon::parse($request->overtime_date . ' ' . $request->start_time);
            $endTime = Carbon::parse($request->overtime_date . ' ' . $request->end_time);

            // If end time is before start time, assume it's next day
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }

            // Calculate duration in hours using diffInMinutes and converting to hours
            $hours = $startTime->diffInMinutes($endTime) / 60;

            $overtime = new Overtime();
            $overtime->employee_id = $user->employee->id;
            $overtime->title = $request->title;
            // $overtime->number_of_days = $request->number_of_days;
            $overtime->overtime_date = $request->overtime_date;
            $overtime->start_time = $request->start_time;
            $overtime->end_time = $request->end_time;
            $overtime->hours = round($hours, 2);
            // $overtime->rate = $request->rate;
            $overtime->status = 'pending';
            $overtime->remark = $request->remark;
            $overtime->created_by = Auth::user()->creatorId();
            $overtime->save();


            return response()->json([
                'status' => true,
                'message' => 'Overtime request successfully created.',
                'data' => [
                    'overtime' => $overtime,
                ],
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
