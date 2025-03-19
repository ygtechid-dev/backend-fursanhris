<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Employee;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the complaints.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Complaint')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get complaints based on permissions
        $query = Complaint::query();


        $complaints = $query->with(['complaintFrom', 'complaintAgainst'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($complaint) use ($companyTz) {
                return $this->formatComplaintResponse($complaint, $companyTz);
            });

        // Count complaints by date
        $complaintCounts = DB::table('complaints')
            ->select(DB::raw('DATE(complaint_date) as date'), DB::raw('count(*) as count'))
            ->where('created_by', Auth::user()->creatorId())
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Complaints retrieved successfully',
            'data' => [
                'complaints' => $complaints,
                'complaint_counts' => $complaintCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created complaint in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Complaint')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'complaint_from' => 'required|exists:employees,id',
            'complaint_against' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'complaint_date' => 'required|date',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $complaint = Complaint::create([
            'complaint_from' => $request->complaint_from,
            'complaint_against' => $request->complaint_against,
            'title' => $request->title,
            'complaint_date' => $request->complaint_date,
            'description' => $request->description,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Complaint created successfully',
            'data' => $this->formatComplaintResponse(
                $complaint->fresh(['complaintFrom', 'complaintAgainst']),
                Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone']
            ),
        ], 201);
    }

    /**
     * Display the specified complaint.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Complaint')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $complaint = Complaint::with(['complaintFrom', 'complaintAgainst'])->find($id);

        if (!$complaint) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        // Check if user has permission to view this complaint
        if (
            $user->type == 'employee' &&
            $complaint->complaint_from != $employee->id &&
            $complaint->complaint_against != $employee->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Complaint retrieved successfully',
            'data' => $this->formatComplaintResponse($complaint, $companyTz),
        ], 200);
    }

    /**
     * Update the specified complaint in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Complaint')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'complaint_from' => 'sometimes|required|exists:employees,id',
            'complaint_against' => 'sometimes|required|exists:employees,id',
            'title' => 'sometimes|required|string|max:255',
            'complaint_date' => 'sometimes|required|date',
            'description' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $complaint->update($request->only([
            'complaint_from',
            'complaint_against',
            'title',
            'complaint_date',
            'description',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Complaint updated successfully',
            'data' => $this->formatComplaintResponse(
                $complaint->fresh(['complaintFrom', 'complaintAgainst']),
                Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone']
            ),
        ], 200);
    }

    /**
     * Remove the specified complaint from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Complaint')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        $complaint->delete();

        return response()->json([
            'status' => true,
            'message' => 'Complaint deleted successfully',
        ], 200);
    }

    /**
     * Get all employees for complaint form (dropdown)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployees()
    {
        if (!Auth::user()->can('Manage Complaint')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $employees = Employee::where('created_by', Auth::user()->creatorId())
            ->select('id', 'name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Employees retrieved successfully',
            'data' => $employees,
        ], 200);
    }

    /**
     * Format the complaint response with additional information
     *
     * @param  \App\Models\Complaint  $complaint
     * @param  string  $timezone
     * @return array
     */
    private function formatComplaintResponse($complaint, $timezone)
    {
        return [
            'id' => $complaint->id,
            'title' => $complaint->title,
            'complaint_date' => $complaint->complaint_date,
            'formatted_date' => \Carbon\Carbon::parse($complaint->complaint_date)
                ->setTimezone($timezone)
                ->format('d M Y'),
            'description' => $complaint->description,
            'complaint_from' => [
                'id' => $complaint->complaintFrom->id,
                'name' => $complaint->complaintFrom->name,
                'employee_id' => $complaint->complaintFrom->employee_id,
                'designation' => $complaint->complaintFrom->designation ? $complaint->complaintFrom->designation->name : null,
                'department' => $complaint->complaintFrom->department ? $complaint->complaintFrom->department->name : null,
            ],
            'complaint_against' => [
                'id' => $complaint->complaintAgainst->id,
                'name' => $complaint->complaintAgainst->name,
                'employee_id' => $complaint->complaintAgainst->employee_id,
                'designation' => $complaint->complaintAgainst->designation ? $complaint->complaintAgainst->designation->name : null,
                'department' => $complaint->complaintAgainst->department ? $complaint->complaintAgainst->department->name : null,
            ],
            'created_at' => \Carbon\Carbon::parse($complaint->created_at)
                ->setTimezone($timezone)
                ->format('d M Y H:i'),
            'updated_at' => \Carbon\Carbon::parse($complaint->updated_at)
                ->setTimezone($timezone)
                ->format('d M Y H:i'),
        ];
    }
}
