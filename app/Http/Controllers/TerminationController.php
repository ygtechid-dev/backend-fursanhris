<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Termination;
use App\Models\TerminationType;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TerminationController extends Controller
{
    /**
     * Display a listing of the terminations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = Auth::user();
        $companyTz = Utility::getCompanySchedule($user->creatorId())['company_timezone'];

        // Query terminations
        $query = Termination::query();

        $terminations = $query->with(['company_created_by', 'user', 'employee', 'terminationType', 'terminatedBy'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($termination) use ($companyTz) {
                return $this->formatTerminationResponse($termination, $companyTz);
            });

        // Count terminations by status
        $terminationCounts = [
            'active' => 0,
            'inactive' => 0,
            'pending' => 0
        ];

        $statusCounts = Termination::select('status', DB::raw('count(*) as count'))
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statusCounts as $status => $count) {
            $terminationCounts[$status] = $count;
        }

        return response()->json([
            'status' => true,
            'message' => 'Terminations retrieved successfully',
            'data' => [
                'terminations' => $terminations,
                'termination_counts' => $terminationCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created termination in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'termination_type_id' => 'required|exists:termination_types,id',
            'termination_date' => 'required|date',
            'notice_date' => 'required|date',
            'reason' => 'nullable|string',
            'description' => 'nullable|string',
            'is_mobile_access_allowed' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $employee = Employee::find($request->employee_id);
        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Handle document uploads
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $key => $document) {
                $filenameWithExt = $document->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $document->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $path = $document->storeAs('termination_documents', $fileNameToStore);
                $documents[] = $path;
            }
        }

        $termination = Termination::create([
            'user_id' => $employee->user_id,
            'employee_id' => $request->employee_id,
            'termination_type_id' => $request->termination_type_id,
            'termination_date' => $request->termination_date,
            'notice_date' => $request->notice_date,
            'reason' => $request->reason,
            'description' => $request->description,
            'is_mobile_access_allowed' => $request->is_mobile_access_allowed ?? false,
            'status' => $request->status,
            'company_id' => Auth::user()->creatorId(),
            'terminated_by' => Auth::user()->id,
            'documents' => $documents,
            'created_by' => Auth::user()->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Termination created successfully',
            'data' => [
                'termination' => $termination->load(['user', 'employee', 'terminationType', 'terminatedBy']),
            ]
        ], 201);
    }

    /**
     * Display the specified termination.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $termination = Termination::with(['user', 'employee', 'terminationType', 'terminatedBy', 'company'])
            ->find($id);

        if (!$termination) {
            return response()->json([
                'status' => false,
                'message' => 'Termination not found',
            ], 404);
        }

        // Check if user has permission to view this termination
        if (Auth::user()->type == 'employee' && $termination->company_id != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $formattedTermination = $this->formatTerminationResponse($termination, $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Termination retrieved successfully',
            'data' => [
                'termination' => $formattedTermination,
            ]
        ], 200);
    }

    /**
     * Update the specified termination in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $termination = Termination::find($id);
        if (!$termination) {
            return response()->json([
                'status' => false,
                'message' => 'Termination not found',
            ], 404);
        }

        // Check if user has permission to update this termination
        if (Auth::user()->type == 'employee' && $termination->company_id != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'termination_type_id' => 'nullable|exists:termination_types,id',
            'termination_date' => 'nullable|date',
            'notice_date' => 'nullable|date',
            'reason' => 'nullable|string',
            'description' => 'nullable|string',
            'is_mobile_access_allowed' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Handle document uploads
        $documents = $termination->documents ?? [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $key => $document) {
                $filenameWithExt = $document->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $document->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $path = $document->storeAs('termination_documents', $fileNameToStore);
                $documents[] = $path;
            }
        }

        $termination->update([
            'termination_type_id' => $request->termination_type_id ?? $termination->termination_type_id,
            'termination_date' => $request->termination_date ?? $termination->termination_date,
            'notice_date' => $request->notice_date ?? $termination->notice_date,
            'reason' => $request->reason ?? $termination->reason,
            'description' => $request->description ?? $termination->description,
            'is_mobile_access_allowed' => $request->is_mobile_access_allowed ?? $termination->is_mobile_access_allowed,
            'status' => $request->status ?? $termination->status,
            'documents' => $documents,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Termination updated successfully',
            'data' => [
                'termination' => $termination->fresh()->load(['user', 'employee', 'terminationType', 'terminatedBy']),
            ]
        ], 200);
    }

    /**
     * Remove the specified termination from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $termination = Termination::find($id);
        if (!$termination) {
            return response()->json([
                'status' => false,
                'message' => 'Termination not found',
            ], 404);
        }

        // Check if user has permission to delete this termination
        if (Auth::user()->type == 'employee' && $termination->company_id != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        // Delete associated documents from storage if needed
        if (!empty($termination->documents)) {
            foreach ($termination->documents as $document) {
                if (file_exists(storage_path('app/' . $document))) {
                    unlink(storage_path('app/' . $document));
                }
            }
        }

        $termination->delete();

        return response()->json([
            'status' => true,
            'message' => 'Termination deleted successfully',
        ], 200);
    }

    /**
     * Get termination types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTerminationTypes()
    {
        if (!Auth::user()->can('Manage Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $terminationTypes = TerminationType::where('created_by', Auth::user()->creatorId())
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Termination types retrieved successfully',
            'data' => [
                'termination_types' => $terminationTypes,
            ]
        ], 200);
    }

    /**
     * Format the termination response.
     *
     * @param  \App\Models\Termination  $termination
     * @param  string  $companyTz
     * @return array
     */
    private function formatTerminationResponse($termination, $companyTz)
    {
        return [
            'id' => $termination->id,
            'employee_id' => $termination->employee->id,
            'employee' => [
                'id' => $termination->employee->id,
                'name' => $termination->employee->name,
                'employee_id' => $termination->employee->employee_id,
                'department' => $termination->employee->department ? $termination->employee->department->name : null,
                'designation' => $termination->employee->designation ? $termination->employee->designation->name : null,
            ],
            'termination_type_id' => $termination->terminationType ? $termination->terminationType->id : null,
            'termination_type' => $termination->terminationType ? $termination->terminationType->name : null,
            'termination_date' => $termination->termination_date->setTimezone($companyTz)->format('Y-m-d'),
            'notice_date' => $termination->notice_date->setTimezone($companyTz)->format('Y-m-d'),
            'reason' => $termination->reason,
            'description' => $termination->description,
            'created_by' => $termination->created_by,
            'company' => $termination->company_created_by,
            'status' => $termination->status,
            'is_mobile_access_allowed' => $termination->is_mobile_access_allowed,
            'terminated_by_id' => $termination->terminatedBy->id,
            'terminated_by' => [
                'id' => $termination->terminatedBy->id,
                'name' => $termination->terminatedBy->employee_name(),
            ],
            'documents' => $termination->documents,
            'created_at' => $termination->created_at->setTimezone($companyTz)->format('Y-m-d H:i:s'),
            'updated_at' => $termination->updated_at->setTimezone($companyTz)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get terminations for a specific employee
     *
     * @param int $employeeId
     * @return \Illuminate\Http\Response
     */
    public function getEmployeeTerminations($employeeId)
    {
        if (!Auth::user()->can('View Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        // Check if user has permission to view this employee's terminations
        if ($employee?->user->type == 'employee' && Auth::user()->id != $employee->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $terminations = Termination::where('employee_id', $employeeId)
            ->with(['user', 'terminatedBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($termination) use ($companyTz) {
                return $this->formatTerminationResponse($termination, $companyTz);
            });

        return response()->json([
            'status' => true,
            'message' => 'Employee terminations retrieved successfully',
            'data' => $terminations,
        ], 200);
    }
}
