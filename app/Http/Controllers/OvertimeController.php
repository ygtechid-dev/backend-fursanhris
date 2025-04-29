<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OvertimeController extends Controller
{
    private function formatOvertimeResponse($overtime, $companyTz)
    {
        return [
            'id' => $overtime->id,
            'employee_id' => $overtime->employee_id,
            'overtime_date' => $overtime->overtime_date,
            'start_time' => $overtime->start_time,
            'end_time' => $overtime->end_time,
            'hours' => $overtime->hours,
            'remark' => $overtime->remark,
            'status' => $overtime->status,
            'approved_at' => Utility::formatDateTimeToCompanyTz($overtime->approved_at, $companyTz)?->format('Y-m-d H:i:s'),
            'rejected_at' => Utility::formatDateTimeToCompanyTz($overtime->rejected_at, $companyTz)?->format('Y-m-d H:i:s'),
            'rejection_reason' => $overtime->rejection_reason,
            'created_by' => $overtime->created_by,
            'company' => $overtime->company,
            'created_at' => $overtime->created_at,
            'updated_at' => $overtime->updated_at,
            'employee' => $overtime->employee,
            'approver' => $overtime->approver,
            'rejecter' => $overtime->rejecter,
        ];
    }

    public function index()
    {
        if (Auth::user()->can('Manage Overtime')) {
            $user = User::with('employee')->where('id', Auth::user()->id)->first();
            $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];

            $overtimes = Overtime::with(['company', 'employee', 'approver', 'rejecter'])
                ->when(Auth::user()->type != 'super admin', function ($q) {
                    $q->where('created_by', Auth::user()->creatorId());
                })
                ->get()
                ->map(function ($overtime) use ($companyTz) {
                    return $this->formatOvertimeResponse($overtime, $companyTz);
                });

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
                    'overtime_date' => 'required|date',
                    'start_time' => 'required',
                    'end_time' => 'required',
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
            $user = Auth::user();

            // Calculate hours between start and end time
            $startTime = Carbon::parse($request->overtime_date . ' ' . $request->start_time);
            $endTime = Carbon::parse($request->overtime_date . ' ' . $request->end_time);
            // dd($startTime, $endTime);
            // If end time is before start time, assume it's next day
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }

            // Calculate duration in hours using diffInMinutes and converting to hours
            $hours = $startTime->diffInMinutes($endTime) / 60;

            try {
                $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];
                $overtime = Overtime::create([
                    'employee_id' => $request->employee_id,
                    'overtime_date' => $request->overtime_date,
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'hours' => round($hours, 2),
                    'status' => 'approved',
                    'remark' => $request->remark,
                    'created_by' => Auth::user()->type == 'super admin' ? $request->created_by :  $user->creatorId(),
                    'approved_by'   => Auth::id(),
                    'approved_at'   => now(),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Overtime request successfully created.',
                    'data' => [
                        'overtime' => $this->formatOvertimeResponse($overtime, $companyTz),
                    ],
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

    public function show($id)
    {
        if (!Auth::user()->can('Manage Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $overtime = Overtime::with(['employee', 'approver', 'rejecter'])->findOrFail($id);

            // Check if the overtime belongs to the current user's company
            if ($overtime->created_by != Auth::user()->creatorId()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Overtime not found.',
                ], 404);
            }

            $user = User::with('employee')->where('id', Auth::user()->id)->first();
            $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];

            return response()->json([
                'status' => true,
                'message' => 'Overtime retrieved successfully.',
                'data' => $this->formatOvertimeResponse($overtime, $companyTz)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve overtime.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'title' => 'required|string|max:255',
            'number_of_days' => 'required|numeric|min:0.1',
            'overtime_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'hours' => 'required|numeric|min:0.1',
            'rate' => 'required|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        try {
            $overtime = Overtime::findOrFail($id);

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
            $employee = Employee::with('user')->findOrFail($request->employee_id);
            if ($employee->created_by != Auth::user()->creatorId()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found in your company.',
                ], 404);
            }

            // Update overtime record
            $overtime->employee_id = $request->employee_id;
            $overtime->title = $request->title;
            $overtime->number_of_days = $request->number_of_days;
            $overtime->overtime_date = $request->overtime_date;
            $overtime->start_time = $request->start_time;
            $overtime->end_time = $request->end_time;
            $overtime->hours = $request->hours;
            $overtime->rate = $request->rate;
            $overtime->remark = $request->remark;
            $overtime->created_by = Auth::user()->type == 'super admin' ? $request->created_by :  $employee->user->creatorId();
            $overtime->save();

            // Load relationships for response
            $overtime->load(['employee', 'approver', 'rejecter']);
            $companyTz = Utility::getCompanySchedule($overtime->created_by)['company_timezone'];

            return response()->json([
                'status' => true,
                'message' => 'Overtime updated successfully.',
                'data' => $this->formatOvertimeResponse($overtime, $companyTz)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update overtime.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected'
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status'   => false,
                'message'   => $messages->first()
            ], 400);
        }

        try {
            $overtime = Overtime::findOrFail($id);

            if ($overtime->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Can only update pending overtime requests.',
                ], 400);
            }

            if ($request->status === 'approved') {
                // Set approval details
                $overtime->approved_by = Auth::id();
                $overtime->approved_at = now();
                $overtime->rejected_by = null;
                $overtime->rejected_at = null;
                $overtime->rejection_reason = null;
            } else {
                // Set rejection details
                $overtime->rejected_by = Auth::id();
                $overtime->rejected_at = now();
                $overtime->approved_by = null;
                $overtime->approved_at = null;
                $overtime->rejection_reason = $request->rejection_reason;
            }

            $overtime->status = $request->status;
            $overtime->save();

            // Load relationships for response
            $overtime->load(['approver', 'rejecter']);
            $companyTz = Utility::getCompanySchedule($overtime->employee->user->creatorId())['company_timezone'];

            return response()->json([
                'status' => true,
                'message' => 'Overtime request ' . $request->status . ' successfully.',
                'data' => [
                    'overtime' => $this->formatOvertimeResponse($overtime, $companyTz),
                    'action_details' => [
                        'status' => $overtime->status,
                        'processed_by' => $request->status === 'approved'
                            ? $overtime->approver->employee_name()
                            : $overtime->rejecter->employee_name(),
                        'processed_at' => $request->status === 'approved'
                            ? $overtime->approved_at
                            : $overtime->rejected_at,
                        'rejection_reason' => $overtime->rejection_reason
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update overtime status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Overtime')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $overtime = Overtime::findOrFail($id);

            // Check if the overtime belongs to the current user's company
            if ($overtime->created_by != Auth::user()->creatorId()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only delete overtime records created by your company.',
                ], 403);
            }

            // Delete the overtime record
            $overtime->delete();

            return response()->json([
                'status' => true,
                'message' => 'Overtime deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete overtime.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
