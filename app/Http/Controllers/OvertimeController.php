<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\User;
use App\Models\Utility;
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

            $overtimes = Overtime::with(['employee', 'approver', 'rejecter'])
                ->where('created_by', '=', $user->creatorId())
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
}
