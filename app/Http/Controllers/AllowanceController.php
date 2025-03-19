<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AllowanceController extends Controller
{
    /**
     * Display a listing of the allowances.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();

        // Query allowances
        $query = Allowance::query();
        $allowances = $query->with(['employee'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Count allowances by type
        $allowanceCounts = DB::table('allowances')
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Allowances retrieved successfully',
            'data' => [
                'allowances' => $allowances,
                'allowance_counts' => $allowanceCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created allowance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:permanent,monthly',
            // 'month' => 'required_if:type,monthly|nullable|integer|min:1|max:12',
            // 'year' => 'required_if:type,monthly|nullable|integer|min:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }



        $data = $request->all();
        $data['created_by'] = Auth::user()->creatorId();

        if ($request->type == 'monthly') {
            $month = date('m');
            $year = date('Y');

            $data['month']      = $month;
            $data['year']      = $year;
        }

        $allowance = Allowance::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Allowance created successfully',
            'data' => $allowance
        ], 201);
    }

    /**
     * Display the specified allowance.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $allowance = Allowance::with('employee')->find($id);

        if (!$allowance) {
            return response()->json([
                'status' => false,
                'message' => 'Allowance not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Allowance retrieved successfully',
            'data' => $allowance
        ], 200);
    }

    /**
     * Update the specified allowance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $allowance = Allowance::find($id);

        if (!$allowance) {
            return response()->json([
                'status' => false,
                'message' => 'Allowance not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|required|exists:employees,id',
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'type' => 'sometimes|required|in:permanent,monthly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if ($request->type == 'monthly' && $allowance->type != 'monthly') {
            $month = date('m');
            $year = date('Y');

            $data['month']      = $month;
            $data['year']      = $year;
        } else {
            $data['month']      = null;
            $data['year']      = null;
        }

        $allowance->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Allowance updated successfully',
            'data' => $allowance
        ], 200);
    }

    /**
     * Remove the specified allowance from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $allowance = Allowance::find($id);

        if (!$allowance) {
            return response()->json([
                'status' => false,
                'message' => 'Allowance not found',
            ], 404);
        }

        $allowance->delete();

        return response()->json([
            'status' => true,
            'message' => 'Allowance deleted successfully'
        ], 200);
    }
}
