<?php

namespace App\Http\Controllers;

use App\Models\Resignation;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResignationController extends Controller
{
    /**
     * Display a listing of resignations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Build query based on user role
        $query = Resignation::query();

        // Only get resignations created by current user's creator
        $query->where('created_by', $user->creatorId());

        $resignations = $query->with(['employee'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($resignation) use ($companyTz) {
                return $this->formatResignationResponse($resignation, $companyTz);
            });

        // Count resignations by status (upcoming and processed)
        $today = date('Y-m-d');
        $resignationCounts = [
            'upcoming' => Resignation::where('created_by', Auth::user()->creatorId())
                ->where('resignation_date', '>', $today)
                ->count(),
            'processed' => Resignation::where('created_by', Auth::user()->creatorId())
                ->where('resignation_date', '<=', $today)
                ->count(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Resignations retrieved successfully',
            'data' => [
                'resignations' => $resignations,
                'resignation_counts' => $resignationCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created resignation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'notice_date' => 'required|date',
            'resignation_date' => 'required|date|after_or_equal:notice_date',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $resignation = Resignation::create([
            'employee_id' => $request->employee_id,
            'notice_date' => $request->notice_date,
            'resignation_date' => $request->resignation_date,
            'description' => $request->description,
            'created_by' => Auth::user()->creatorId(),
        ]);

        // Get employee details for response
        $resignation->load('employee');
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Resignation created successfully',
            'data' => $this->formatResignationResponse($resignation, $companyTz),
        ], 201);
    }

    /**
     * Display the specified resignation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('Show Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $resignation = Resignation::with('employee')->find($id);

        if (!$resignation || $resignation->created_by != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Resignation not found.',
            ], 404);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Resignation retrieved successfully',
            'data' => $this->formatResignationResponse($resignation, $companyTz),
        ], 200);
    }

    /**
     * Update the specified resignation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $resignation = Resignation::find($id);

        if (!$resignation || $resignation->created_by != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Resignation not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|required|exists:employees,id',
            'notice_date' => 'sometimes|required|date',
            'resignation_date' => 'sometimes|required|date|after_or_equal:notice_date',
            'description' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $resignation->update($request->only([
            'employee_id',
            'notice_date',
            'resignation_date',
            'description',
        ]));

        // Get updated resignation with employee details
        $resignation->load('employee');
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Resignation updated successfully',
            'data' => $this->formatResignationResponse($resignation, $companyTz),
        ], 200);
    }

    /**
     * Remove the specified resignation from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $resignation = Resignation::find($id);

        if (!$resignation || $resignation->created_by != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Resignation not found.',
            ], 404);
        }

        $resignation->delete();

        return response()->json([
            'status' => true,
            'message' => 'Resignation deleted successfully',
        ], 200);
    }

    /**
     * Format resignation response data.
     *
     * @param  \App\Models\Resignation  $resignation
     * @param  string  $companyTz
     * @return array
     */
    private function formatResignationResponse($resignation, $companyTz)
    {
        $employeeName = "";
        if ($resignation->employee) {
            $employeeName = $resignation->employee->name;
        }

        return [
            'id' => $resignation->id,
            'employee_id' => $resignation->employee_id,
            'employee_name' => $employeeName,
            'notice_date' => $resignation->notice_date,
            'notice_date_formatted' => \Carbon\Carbon::parse($resignation->notice_date)
                ->setTimezone($companyTz)
                ->format('d M Y'),
            'resignation_date' => $resignation->resignation_date,
            'resignation_date_formatted' => \Carbon\Carbon::parse($resignation->resignation_date)
                ->setTimezone($companyTz)
                ->format('d M Y'),
            'description' => $resignation->description,
            'status' => date('Y-m-d') > $resignation->resignation_date ? 'Processed' : 'Upcoming',
            'created_at' => $resignation->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $resignation->created_at
                ->setTimezone($companyTz)
                ->format('d M Y, H:i'),
        ];
    }
}
