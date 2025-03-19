<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    /**
     * Display a listing of promotions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Promotion')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $promotions = Promotion::where('created_by', Auth::user()->creatorId())
            ->with(['employee', 'designation'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($promotion) {
                return $this->formatPromotionResponse($promotion);
            });

        // Count promotions by month
        $promotionsByMonth = Promotion::where('created_by', Auth::user()->creatorId())
            ->select(DB::raw('MONTH(promotion_date) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(promotion_date)'))
            ->pluck('count', 'month')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Promotions retrieved successfully',
            'data' => [
                'promotions' => $promotions,
                'promotions_by_month' => $promotionsByMonth,
            ]
        ], 200);
    }

    /**
     * Store a newly created promotion in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Promotion')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'designation_id' => 'required|exists:designations,id',
            'promotion_title' => 'required|string|max:255',
            'promotion_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $promotion = Promotion::create([
            'employee_id' => $request->employee_id,
            'designation_id' => $request->designation_id,
            'promotion_title' => $request->promotion_title,
            'promotion_date' => $request->promotion_date,
            'description' => $request->description,
            'created_by' => Auth::user()->creatorId()
        ]);

        // Update employee's designation
        $employee = Employee::find($request->employee_id);
        if ($employee) {
            $employee->designation_id = $request->designation_id;
            $employee->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Promotion created successfully',
            'data' => $this->formatPromotionResponse($promotion->load(['employee', 'designation']))
        ], 201);
    }

    /**
     * Display the specified promotion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('Show Promotion')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $promotion = Promotion::with(['employee', 'designation'])
            ->where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$promotion) {
            return response()->json([
                'status' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Promotion retrieved successfully',
            'data' => $this->formatPromotionResponse($promotion)
        ], 200);
    }

    /**
     * Update the specified promotion in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Promotion')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $promotion = Promotion::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$promotion) {
            return response()->json([
                'status' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'designation_id' => 'required|exists:designations,id',
            'promotion_title' => 'required|string|max:255',
            'promotion_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $promotion->update([
            'employee_id' => $request->employee_id,
            'designation_id' => $request->designation_id,
            'promotion_title' => $request->promotion_title,
            'promotion_date' => $request->promotion_date,
            'description' => $request->description
        ]);

        // Update employee's designation if needed
        if ($request->has('designation_id')) {
            $employee = Employee::find($request->employee_id);
            if ($employee) {
                $employee->designation_id = $request->designation_id;
                $employee->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Promotion updated successfully',
            'data' => $this->formatPromotionResponse($promotion->fresh()->load(['employee', 'designation']))
        ], 200);
    }

    /**
     * Remove the specified promotion from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Promotion')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $promotion = Promotion::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$promotion) {
            return response()->json([
                'status' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        $promotion->delete();

        return response()->json([
            'status' => true,
            'message' => 'Promotion deleted successfully'
        ], 200);
    }

    /**
     * Format promotion response.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return array
     */
    private function formatPromotionResponse($promotion)
    {
        $employeeName = null;
        $employeeId = null;
        $designationName = null;

        if ($promotion->employee) {
            $employeeName = $promotion->employee->name;
            $employeeId = $promotion->employee->employee_id;
        }

        if ($promotion->designation) {
            $designationName = $promotion->designation->name;
        }

        return [
            'id' => $promotion->id,
            'employee_id' => $promotion->employee_id,
            'employee_name' => $employeeName,
            'employee_code' => $employeeId,
            'designation_id' => $promotion->designation_id,
            'designation_name' => $designationName,
            'promotion_title' => $promotion->promotion_title,
            'promotion_date' => $promotion->promotion_date,
            'description' => $promotion->description,
            'created_at' => $promotion->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $promotion->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
