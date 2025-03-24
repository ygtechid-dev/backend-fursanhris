<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utility;
use App\Models\Warning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WarningController extends Controller
{
    /**
     * Display a listing of the warnings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Warning')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get warnings based on role
        // If user has admin role or can manage all warnings, show all warnings
        // Otherwise show only warnings related to the employee
        $query = Warning::query();

        // if (!Auth::user()->can('Manage All Warnings')) {
        //     // Show warnings where current employee is the recipient or issuer
        //     $query->where(function ($q) use ($employee) {
        //         $q->where('warning_to', $employee->id)
        //             ->orWhere('warning_by', $employee->id);
        //     });
        // }

        $warnings = $query->with(['warningTo.user', 'warningBy.user'])
            ->where('created_by', Auth::user()->creatorId())
            ->orderBy('warning_date', 'desc')
            ->get()
            ->map(function ($warning) use ($companyTz) {
                return $this->formatWarningResponse($warning, $companyTz);
            });

        // Count warnings by month (last 6 months)
        $warningsByMonth = [];
        $currentMonth = now();

        for ($i = 0; $i < 6; $i++) {
            $month = $currentMonth->copy()->subMonths($i);
            $count = Warning::where('created_by', Auth::user()->creatorId())
                ->whereYear('warning_date', $month->year)
                ->whereMonth('warning_date', $month->month)
                ->count();

            $warningsByMonth[$month->format('M Y')] = $count;
        }

        return response()->json([
            'status' => true,
            'message' => 'Warnings retrieved successfully',
            'data' => [
                'warnings' => $warnings,
                'warnings_by_month' => $warningsByMonth,
            ]
        ], 200);
    }

    /**
     * Store a newly created warning in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Warning')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'warning_to' => 'required|exists:employees,id',
            'warning_by' => 'required|exists:employees,id',
            'subject' => 'required|string|max:255',
            'warning_date' => 'required|date',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $warning = Warning::create([
            'warning_to' => $request->warning_to,
            'warning_by' => $request->warning_by,
            'subject' => $request->subject,
            'warning_date' => $request->warning_date,
            'description' => $request->description,
            'created_by' => Auth::user()->creatorId(),
        ]);

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $warning = $this->formatWarningResponse($warning->fresh(['warningTo.user', 'warningBy.user']), $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Warning created successfully',
            'data' => $warning,
        ], 201);
    }

    /**
     * Display the specified warning.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Warning')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $warning = Warning::with(['warningTo.user', 'warningBy.user'])
            ->where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$warning) {
            return response()->json([
                'status' => false,
                'message' => 'Warning not found',
            ], 404);
        }

        // Check if user has permission to view this specific warning
        // if (!Auth::user()->can('Manage All Warnings')) {
        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        if (
            $user->type == 'employee' &&
            $warning->warning_to != $employee->id &&
            $warning->warning_by != $employee->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
        // }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $warning = $this->formatWarningResponse($warning, $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Warning retrieved successfully',
            'data' => $warning,
        ], 200);
    }

    /**
     * Update the specified warning in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Warning')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $warning = Warning::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$warning) {
            return response()->json([
                'status' => false,
                'message' => 'Warning not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'warning_to' => 'sometimes|required|exists:employees,id',
            'warning_by' => 'sometimes|required|exists:employees,id',
            'subject' => 'sometimes|required|string|max:255',
            'warning_date' => 'sometimes|required|date',
            'description' => 'sometimes|required|string',
        ]);



        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $warning->update($request->only([
            'warning_to',
            'warning_by',
            'subject',
            'warning_date',
            'description',
        ]));

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $warning = $this->formatWarningResponse($warning->fresh(['warningTo.user', 'warningBy.user']), $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Warning updated successfully',
            'data' => $warning,
        ], 200);
    }

    /**
     * Remove the specified warning from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Warning')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $warning = Warning::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$warning) {
            return response()->json([
                'status' => false,
                'message' => 'Warning not found',
            ], 404);
        }

        $warning->delete();

        return response()->json([
            'status' => true,
            'message' => 'Warning deleted successfully',
        ], 200);
    }

    /**
     * Format warning response.
     *
     * @param  \App\Models\Warning  $warning
     * @param  string  $timezone
     * @return array
     */
    private function formatWarningResponse($warning, $timezone)
    {
        return [
            'id' => $warning->id,
            'subject' => $warning->subject,
            'description' => $warning->description,
            'warning_date' => [
                'raw' => $warning->warning_date,
                'formatted' => \Carbon\Carbon::parse($warning->warning_date)
                    ->timezone($timezone)
                    ->format('M d, Y'),
            ],
            'warning_to' => [
                'id' => $warning->warningTo->id,
                'name' => $warning->warningTo->name,
                'employee_id' => $warning->warningTo->employee_id,
                'email' => $warning->warningTo->email,
                'user' => $warning->warningTo->user ? [
                    'id' => $warning->warningTo->user->id,
                    'name' => $warning->warningTo->user->name,
                ] : null,
            ],
            'warning_by' => [
                'id' => $warning->warningBy->id,
                'name' => $warning->warningBy->name,
                'employee_id' => $warning->warningBy->employee_id,
                'user' => $warning->warningBy->user ? [
                    'id' => $warning->warningBy->user->id,
                    'name' => $warning->warningBy->user->name,
                ] : null,
            ],
            'created_at' => [
                'raw' => $warning->created_at,
                'formatted' => \Carbon\Carbon::parse($warning->created_at)
                    ->timezone($timezone)
                    ->format('M d, Y H:i'),
            ],
            'updated_at' => [
                'raw' => $warning->updated_at,
                'formatted' => \Carbon\Carbon::parse($warning->updated_at)
                    ->timezone($timezone)
                    ->format('M d, Y H:i'),
            ],
        ];
    }
}
