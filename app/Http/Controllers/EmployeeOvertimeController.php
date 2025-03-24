<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeOvertimeController extends Controller
{
    function getOvertimes($id)
    {
        if (!Auth::user()->can('Manage Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
        $datas = Overtime::where('employee_id', $id)
            ->where('status', 'approved')
            ->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Overtimes successfullly retrieved',
            'data'      => $datas
        ], 200);
    }

    public function store(Request $request, $id)
    {
        if (Auth::user()->can('Create Overtime')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'employee_id' => 'nullable',
                    'title' => 'nullable',
                    'overtime_date' => 'required|date',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'rate' => 'required|numeric|min:0',
                    'remark' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
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

            try {
                // $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
                $overtime = Overtime::create([
                    'employee_id' => $id,
                    'overtime_date' => $request->overtime_date,
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'hours' => round($hours, 2),
                    'rate' => $request->rate,
                    'status' => 'approved',
                    'remark' => $request->remark,
                    'created_by' => Auth::user()->creatorId(),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Overtime request successfully created.',
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create overtime request.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function update(Request $request, $id, $overtimeId)
    {
        if (!Auth::user()->can('Edit Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable',
            'title' => 'nullable',
            'overtime_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'remark' => 'nullable',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        try {
            $overtime = Overtime::findOrFail($overtimeId);

            // Check if the overtime belongs to the current user's company
            if ($overtime->created_by != Auth::user()->creatorId()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Overtime not found.',
                ], 404);
            }

            // Check if the overtime status is pending
            if ($overtime->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only pending overtime requests can be updated.',
                ], 400);
            }

            // Check if employee exists and belongs to the company
            $employee = Employee::findOrFail($id);
            if ($employee->created_by != Auth::user()->creatorId()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found in your company.',
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

            // Update overtime record
            $overtime->employee_id = $employee->id;
            $overtime->title = $request->title ?? null;
            $overtime->overtime_date = $request->overtime_date;
            $overtime->start_time = $request->start_time;
            $overtime->end_time = $request->end_time;
            $overtime->hours = $hours;
            $overtime->remark = $request->remark;
            $overtime->save();

            return response()->json([
                'status' => true,
                'message' => 'Overtime updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update overtime.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id, $overtimeId)
    {
        if (!Auth::user()->can('Delete Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $obj = Overtime::find($overtimeId);

        if (!$obj) {
            return response()->json([
                'status' => false,
                'message' => 'Overtime not found',
            ], 404);
        }

        $obj->delete();

        return response()->json([
            'status' => true,
            'message' => 'Overtime deleted successfully',
        ], 200);
    }
}
