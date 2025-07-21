<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Utility;
use App\Services\NotificationService;
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
            'company' => $leave->company,
            'created_at' => $leave->created_at,
            'updated_at' => $leave->updated_at,
            'employee' => $leave->employee,
            'leave_type' => $leave->leaveType,
            'approver' => $leave->approver,
            'rejecter' => $leave->rejecter,
            'document_path' => $leave->document_path,

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
                'company',
                'employee',
                'leaveType',
                'approver',
                'rejecter'
            ])
                ->when(Auth::user()->type != 'super admin', function ($q) {
                    $q->where('created_by', Auth::user()->creatorId());
                })
                ->get()
                ->map(function ($leave) use ($companyTz) {
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
                'remark' => 'nullable',
                'document_path' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status'   => false,
                'message'   => $messages->first()
            ], 400);
        }

        $employee = Employee::with('user')->find($request->employee_id);
        if (empty($employee)) {
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
        $overlapping_leaves = Leave::where('employee_id', $employee->id)
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
        $leaves_used = Leave::where('employee_id', $employee->id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Calculate pending leaves
        $leaves_pending = Leave::where('employee_id', $employee->id)
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
            $companyTz = Utility::getCompanySchedule($employee?->user->creatorId())['company_timezone'];

            $documentPath = null;
            if ($request->hasFile('document_path')) {
                $receipt = $request->file('document_path');
                $fileName = time() . '-' . $receipt->getClientOriginalName();
                $filePath = $receipt->storeAs('leave_documents', $fileName, 'public');
                $documentPath = url('/storage/' . $filePath);
            }

            $leave = Leave::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leave_type->id,
                'applied_on' => now()->format('Y-m-d'),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_leave_days' => $total_leave_days,
                'leave_reason' => $request->leave_reason,
                'emergency_contact' => $request->emergency_contact ?? null,
                'remark' => $request->remark,
                'status' => 'approved',
                'created_by' => Auth::user()->type == 'super admin' ? $request->created_by :  $employee?->user->creatorId(),
                'approved_by'   => Auth::id(),
                'approved_at'   => now(),
                'document_path' => $documentPath,
            ]);



            return response()->json([
                'status' => true,
                'message' => 'Leave created successfully.',
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
                'message' => 'Failed to create leave.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Leave')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $leave = Leave::find($id);
        if (!$leave) {
            return response()->json([
                'status' => false,
                'message' => 'Leave not found.',
            ], 404);
        }

        // Check if the user is authorized to update this leave
        // Only allow if it's their leave or they have admin privileges
        // if ($leave->employee->user_id != Auth::id() && !Auth::user()->can('Manage All Leaves')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You are not authorized to update this leave.',
        //     ], 403);
        // }

        // If the leave is already approved or rejected, don't allow updates
        if (in_array($leave->status, ['approved', 'rejected'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot update leave that has already been ' . $leave->status . '.',
            ], 400);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'leave_type_id' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_reason' => 'required',
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

        // Check for overlapping leaves (excluding the current leave being updated)
        $overlapping_leaves = Leave::where('employee_id', $leave->employee_id)
            ->where('id', '!=', $leave->id) // Exclude current leave
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

        // Calculate used leaves (excluding current leave if it was approved)
        $leaves_used = Leave::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'approved')
            ->where('id', '!=', $leave->id) // Exclude current leave
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Calculate pending leaves (excluding current leave)
        $leaves_pending = Leave::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'pending')
            ->where('id', '!=', $leave->id) // Exclude current leave
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

        // Update leave request
        try {
            $companyTz = Utility::getCompanySchedule($leave->employee->user->creatorId())['company_timezone'];

            $employee = Employee::with('user')->find($request->employee_id);
            if (empty($employee)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            $leave->update([
                'leave_type_id' => $leave_type->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_leave_days' => $total_leave_days,
                'leave_reason' => $request->leave_reason,
                'emergency_contact' => $request->emergency_contact ?? $leave->emergency_contact,
                'remark' => $request->remark,
                'status' => 'pending', // Reset to pending since leave details changed
                'created_by' => Auth::user()->type == 'super admin' ? $request->created_by :  $employee?->user->creatorId(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Leave updated successfully.',
                'data' => [
                    'leave' => $this->formatLeaveResponse($leave, $companyTz),
                    'leave_type' => $leave_type->title,
                    'total_days' => $total_leave_days,
                    'remaining_days' => $remaining_leaves - $total_leave_days,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update leave.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Leave')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $leave = Leave::find($id);
        if (!$leave) {
            return response()->json([
                'status' => false,
                'message' => 'Leave not found.',
            ], 404);
        }

        // Check if the user is authorized to delete this leave
        // Only allow if it's their leave or they have admin privileges
        // if ($leave->employee->user_id != Auth::id() && !Auth::user()->can('Manage All Leaves')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You are not authorized to delete this leave.',
        //     ], 403);
        // }

        // Don't allow deletion of approved leaves that have already been taken
        if ($leave->status == 'approved' && Carbon::parse($leave->start_date)->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete leave that has already been taken.',
            ], 400);
        }

        try {
            $leave->delete();

            return response()->json([
                'status' => true,
                'message' => 'Leave deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete leave.',
                'error' => $e->getMessage(),
            ], 500);
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
            $notificationService = new NotificationService();

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

            if ($request->has('remark')) {
                $leave->remark = $request->remark;
            }

            $leave->save();

            if ($leave->status === 'approved') {
                $notificationService->notifyLeaveApproved($leave?->employee?->user, $leave->toArray());
            } else {
                $notificationService->notifyLeaveRejected($leave?->employee?->user, $leave->toArray(), $leave->remark);
            }

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
