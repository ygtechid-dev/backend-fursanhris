<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{

    private function formatLeaveResponse($leave, $companyTz)
    {
        return [
            'id' => $leave->id,
            'employee_id' => $leave->employee_id,
            'leave_type_id' => $leave->leave_type_id,
            'applied_on' => $leave->applied_on,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_leave_days' => $leave->total_leave_days,
            'leave_reason' => $leave->leave_reason,
            'emergency_contact' => $leave->emergency_contact,
            'remark' => $leave->remark,
            'status' => $leave->status,
            'approved_at' => Utility::formatDateTimeToCompanyTz($leave->approved_at, $companyTz)?->format('Y-m-d H:i:s'),
            'rejected_at' => Utility::formatDateTimeToCompanyTz($leave->rejected_at, $companyTz)?->format('Y-m-d H:i:s'),
            'created_by' => $leave->created_by,
            'created_at' => $leave->created_at,
            'updated_at' => $leave->updated_at,
            'employee' => $leave->employee,
            'leave_type' => $leave->leaveType,
            'approver' => $leave->approver,
            'rejecter' => $leave->rejecter,

        ];
    }

    public function index()
    {
        if (Auth::user()->can('Manage Leave')) {
            $user     = User::with('employee')
                ->where('id', Auth::user()->id)->first();

            $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];
            $leaves = Leave::with([
                'employee',
                'leaveType',
                'approver',
                'rejecter'
            ])
                ->where('employee_id', '=', $user?->employee->id)->get()
                ->map(function ($leave) use ($companyTz) {
                    return $this->formatLeaveResponse($leave, $companyTz);
                });;

            return response()->json([
                'status' => true,
                'message' => 'Leave retrieved successfully',
                'data' => [
                    'leaves' => $leaves,
                    'remaining_leave' => $this->checkRemainingLeave()?->original['data'],
                ]
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
        if (!Auth::user()->can('Create Leave')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'employee_id' => 'required',
                'leave_type_id' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_reason' => 'required',
                'emergency_contact' => 'nullable',
                'remark' => 'nullable',
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

        $leave_type = LeaveType::find($request->leave_type_id);
        if (!$leave_type) {
            return response()->json([
                'status' => false,
                'message' => 'Leave type not found.',
            ], 404);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date)->addDay(); // Add one day to include the end date
        $total_leave_days = $startDate->diffInDays($endDate);

        // Check for overlapping leaves
        $overlapping_leaves = Leave::where('employee_id', $user->employee->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        if ($overlapping_leaves->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'You already have leave requests that overlap with these dates.',
                'data' => [
                    'conflicting_leaves' => $overlapping_leaves->map(function ($leave) {
                        return [
                            'start_date' => $leave->start_date,
                            'end_date' => $leave->end_date,
                            'status' => $leave->status,
                            'leave_type' => $leave->leaveType->title
                        ];
                    })
                ],
            ], 400);
        }

        // Get annual leave cycle dates
        $date = Utility::AnnualLeaveCycle();

        // Calculate used leaves
        $leaves_used = Leave::where('employee_id', $user->employee->id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Calculate pending leaves
        $leaves_pending = Leave::where('employee_id', $user->employee->id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'pending')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        $remaining_leaves = $leave_type->days - $leaves_used;

        // Check total leave balance including pending leaves
        $total_requested = $leaves_pending + $total_leave_days;
        if ($total_requested > $remaining_leaves) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient leave balance when including pending requests.',
                'data' => [
                    'leave_balance' => $remaining_leaves,
                    'pending_leaves' => $leaves_pending,
                    'requested_days' => $total_leave_days,
                    'total_requested' => $total_requested,
                ],
            ], 400);
        }

        // Create leave request
        try {
            $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];
            $leave = Leave::create([
                'employee_id' => $user->employee->id,
                'leave_type_id' => $leave_type->id,
                'applied_on' => now()->format('Y-m-d'),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_leave_days' => $total_leave_days,
                'leave_reason' => $request->leave_reason,
                'emergency_contact' => $request->emergency_contact,
                'remark' => $request->remark,
                'status' => 'pending',
                'created_by' => $user->creatorId(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Leave request created successfully.',
                'data' => [
                    'leave' => $this->formatLeaveResponse($leave, $companyTz),
                    'leave_type' => $leave_type->title,
                    'total_days' => $total_leave_days,
                    'remaining_days' => $remaining_leaves - $total_leave_days,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create leave request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkRemainingLeave()
    {
        try {
            if (!Auth::user()->can('Manage Leave')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Permission denied.',
                ], 403);
            }

            $user = Auth::user();
            if (!$user || !$user->employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Get annual leave cycle dates
            $date = Utility::AnnualLeaveCycle();

            // Get all leave types
            $leaveTypes = LeaveType::all();
            $leaveBalance = [];

            foreach ($leaveTypes as $leaveType) {
                // Calculate used leaves (both approved and pending)
                $usedLeaves = Leave::where('employee_id', $user->employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->whereIn('status', ['approved', 'pending'])
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');

                // Calculate remaining leaves
                $remainingLeaves = $leaveType->days - $usedLeaves;

                $leaveBalance[] = [
                    'id' => $leaveType->id,
                    'leave_type' => $leaveType->title,
                    'used' => (int)$usedLeaves,
                    'remaining' => max(0, (int)$remainingLeaves), // Memastikan nilai tidak negatif
                ];
            }

            return response()->json([
                'status' => true,
                'data' => $leaveBalance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch leave balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
