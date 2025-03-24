<?php

namespace App\Http\Controllers;

use App\Models\TerminationType;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TerminationTypeController extends Controller
{
    /**
     * Display a listing of the termination types.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get termination types based on permissions
        $query = TerminationType::query();
        $terminationTypes = $query
            ->where('created_by', Auth::user()->creatorId())
            ->orderBy('created_at', 'desc')
            ->get();

        // Count terminations by type
        $terminationCounts = DB::table('terminations')
            ->select('termination_type_id', DB::raw('count(*) as count'))
            ->where('created_by', Auth::user()->creatorId())
            ->groupBy('termination_type_id')
            ->pluck('count', 'termination_type_id')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Termination types retrieved successfully',
            'data' => [
                'termination_types' => $terminationTypes,
                'termination_counts' => $terminationCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created termination type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $terminationType = new TerminationType();
        $terminationType->name = $request->name;
        $terminationType->created_by = Auth::user()->creatorId();
        $terminationType->save();

        return response()->json([
            'status' => true,
            'message' => 'Termination type created successfully',
            'data' => $terminationType,
        ], 201);
    }

    /**
     * Display the specified termination type.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('View Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $terminationType = TerminationType::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$terminationType) {
            return response()->json([
                'status' => false,
                'message' => 'Termination type not found',
            ], 404);
        }

        // Get related terminations
        $terminations = DB::table('terminations')
            ->where('termination_type_id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Termination type retrieved successfully',
            'data' => [
                'termination_type' => $terminationType,
                'terminations_count' => $terminations,
            ]
        ], 200);
    }

    /**
     * Update the specified termination type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $terminationType = TerminationType::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$terminationType) {
            return response()->json([
                'status' => false,
                'message' => 'Termination type not found',
            ], 404);
        }

        $terminationType->name = $request->name;
        $terminationType->save();

        return response()->json([
            'status' => true,
            'message' => 'Termination type updated successfully',
            'data' => $terminationType,
        ], 200);
    }

    /**
     * Remove the specified termination type from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Termination')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $terminationType = TerminationType::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$terminationType) {
            return response()->json([
                'status' => false,
                'message' => 'Termination type not found',
            ], 404);
        }

        // Check if there are any terminations using this type
        $terminationsCount = DB::table('terminations')
            ->where('termination_type_id', $id)
            ->count();

        if ($terminationsCount > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete termination type that is being used',
                'data' => [
                    'terminations_count' => $terminationsCount,
                ]
            ], 422);
        }

        $terminationType->delete();

        return response()->json([
            'status' => true,
            'message' => 'Termination type deleted successfully',
        ], 200);
    }
}
