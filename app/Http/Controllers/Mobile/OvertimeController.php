<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
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
                ->where('employee_id', '=', $user?->employee->id)
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
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i',
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
                $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];
                $overtime = Overtime::create([
                    'employee_id' => $user->employee->id,
                    'overtime_date' => $request->overtime_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'hours' => round($hours, 2),
                    'status' => 'pending',
                    'remark' => $request->remark,
                    'created_by' => $user->creatorId(),
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
}
