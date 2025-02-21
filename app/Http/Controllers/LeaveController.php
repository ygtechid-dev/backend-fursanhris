<?php

namespace App\Http\Controllers;

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
        // anti ada logic untuk superadmin bisa akses semuanya
        if (Auth::user()->can('Manage Leave')) {

            /** For ADmin Company not for superadmin */
            $user     = User::with('employee')
                ->where('id', Auth::user()->id)->first();
            $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];
            $leaves = Leave::with([
                'employee',
                'leaveType',
                'approver',
                'rejecter'
            ])->where('created_by', '=', $user->creatorId())->get()->map(function ($leave) use ($companyTz) {
                return $this->formatLeaveResponse($leave, $companyTz);
            });
            /** For ADmin Company not for superadmin */

            return response()->json([
                'status' => true,
                'message' => 'Leave retrieved successfully',
                'data' => $leaves
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    /** Untuk Admin dashboard / bukan employee */
    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Leave')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'remark' => 'required_if:status,rejected'
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status'   => false,
                'message'   => $messages->first()
            ], 400);
        }

        try {
            $leave = Leave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Can only update pending leave requests.',
                ], 400);
            }

            // If approving, check leave balance
            if ($request->status === 'approved') {
                $leave_type = LeaveType::find($leave->leave_type_id);
                $date = Utility::AnnualLeaveCycle();

                $leaves_used = Leave::where('employee_id', $leave->employee_id)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('status', 'approved')
                    ->where('id', '!=', $id)
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');

                $remaining_leaves = $leave_type->days - $leaves_used;

                if ($leave->total_leave_days > $remaining_leaves) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient leave balance.',
                        'data' => [
                            'leave_balance' => $remaining_leaves,
                            'requested_days' => $leave->total_leave_days,
                        ],
                    ], 400);
                }

                // Set approval details
                $leave->approved_by = Auth::id();
                $leave->approved_at = now();
                $leave->rejected_by = null;
                $leave->rejected_at = null;
            } else {
                // Set rejection details
                $leave->rejected_by = Auth::id();
                $leave->rejected_at = now();
                $leave->approved_by = null;
                $leave->approved_at = null;
            }

            $leave->status = $request->status;
            $leave->remark = $request->remark;
            $leave->save();

            // Load relationships for response
            $leave->load(['approver', 'rejecter']);
            $companyTz = Utility::getCompanySchedule($leave->employee->user->creatorId())['company_timezone'];

            return response()->json([
                'status' => true,
                'message' => 'Leave request ' . $request->status . ' successfully.',
                'data' => [
                    'leave' => $this->formatLeaveResponse($leave, $companyTz),
                    'action_details' => [
                        'status' => $leave->status,
                        'processed_by' => $request->status === 'approved'
                            ? $leave->approver->employee_name()
                            : $leave->rejecter->employee_name(),
                        'processed_at' => $request->status === 'approved'
                            ? $leave->approved_at
                            : $leave->rejected_at,
                        'remark' => $leave->remark
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update leave status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Optional: Add method to get all pending leaves for approval
    public function getPendingLeaves()
    {
        if (!Auth::user()->can('Manage Leave')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $pendingLeaves = Leave::with(['employee', 'leaveType'])
                ->where('status', 'pending')
                ->get()
                ->map(function ($leave) {
                    return [
                        'id' => $leave->id,
                        'employee_name' => $leave->employee->name,
                        'leave_type' => $leave->leaveType->title,
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'total_days' => $leave->total_leave_days,
                        'reason' => $leave->leave_reason,
                        'applied_on' => $leave->applied_on,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Pending leaves retrieved successfully.',
                'data' => $pendingLeaves
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch pending leaves.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
