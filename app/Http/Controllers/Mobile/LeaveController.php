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
    public function index()
    {
        if (Auth::user()->can('Manage Leave')) {
            $user     = User::with('employee')->where('id', Auth::user()->id)->first();
            $leaves = Leave::where('employee_id', '=', $user?->employee->id)->get();

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

    // public function store(Request $request)
    // {
    //     if (!Auth::user()->can('Create Leave')) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Permission denied.',
    //         ], 403);
    //     }

    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'employee_id' => 'required',
    //             'leave_type_id' => 'required',
    //             'start_date' => 'required',
    //             'end_date' => 'required',
    //             'leave_reason' => 'required',
    //             'emergency_contact' => 'nullable',
    //             'remark' => 'nullable',
    //         ]
    //     );

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->getMessageBag()->first(),
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }
    //     $user = Auth::user();

    //     if (!$user || !$user->employee) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Employee not found',
    //         ], 404);
    //     }

    //     $leave_type = LeaveType::find($request->leave_type_id);
    //     if (!$leave_type) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Leave type not found.',
    //         ], 404);
    //     }
    //     // dd(Utility::checkLeaveRemaining($request->employee_id, $leave_type->id));
    //     $startDate = new \DateTime($request->start_date);
    //     $endDate = new \DateTime($request->end_date);
    //     $endDate->add(new \DateInterval('P1D'));
    //     $date = Utility::AnnualLeaveCycle();

    //     // Calculate leave days
    //     $leaves_used = Leave::where('employee_id', $user->employee->id)
    //         ->where('leave_type_id', $leave_type->id)
    //         ->where('status', 'approved')
    //         ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
    //         ->sum('total_leave_days');

    //     $leaves_pending = Leave::where('employee_id', $user->employee->id)
    //         ->where('leave_type_id', $leave_type->id)
    //         ->where('status', 'pending')
    //         ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
    //         ->sum('total_leave_days');
    //     $leaves_pending = intval($leaves_pending);

    //     $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
    //     $remaining_leaves = $leave_type->days - $leaves_used;

    //     // Check if eligible for leave
    //     if ($total_leave_days > $remaining_leaves) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'You are not eligible for leave.',
    //             'data' => [
    //                 'requested_days' => $total_leave_days,
    //                 'remaining_days' => $remaining_leaves,
    //             ],
    //         ], 400);
    //     }

    //     // Check pending leaves
    //     dd($leaves_pending, $total_leave_days, $remaining_leaves);
    //     if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $remaining_leaves) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Multiple leave entry is pending.',
    //             'data' => [
    //                 'pending_days' => $leaves_pending,
    //                 'requested_days' => $total_leave_days,
    //                 'remaining_days' => $remaining_leaves,
    //             ],
    //         ], 400);
    //     }

    //     // Check if leave days are within allowed limit
    //     if ($leave_type->days < $total_leave_days) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => "Leave type {$leave_type->title} provides maximum {$leave_type->days} days. Please ensure your selected days are within this limit.",
    //             'data' => [
    //                 'max_allowed_days' => $leave_type->days,
    //                 'requested_days' => $total_leave_days,
    //             ],
    //         ], 400);
    //     }
    //     // Create leave request
    //     try {
    //         $leave = new Leave();
    //         $leave->employee_id = $user->employee->id;
    //         $leave->leave_type_id = $request->leave_type_id;
    //         $leave->applied_on = date('Y-m-d');
    //         $leave->start_date = $request->start_date;
    //         $leave->end_date = $request->end_date;
    //         $leave->total_leave_days = $total_leave_days;
    //         $leave->leave_reason = $request->leave_reason;
    //         $leave->emergency_contact = $request->emergency_contact;
    //         $leave->remark = $request->remark;
    //         $leave->status = 'pending';
    //         $leave->created_by = Auth::user()->creatorId();
    //         $leave->save();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Leave request created successfully.',
    //             'data' => [
    //                 'leave' => $leave,
    //                 'leave_type' => $leave_type->title,
    //                 'total_days' => $total_leave_days,
    //                 'remaining_days' => $remaining_leaves - $total_leave_days,
    //             ],
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to create leave request.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

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
                    'leave' => $leave,
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
