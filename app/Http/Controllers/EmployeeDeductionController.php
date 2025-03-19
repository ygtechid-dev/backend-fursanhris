<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeDeductionController extends Controller
{
    function getDeductions($id)
    {
        if (!Auth::user()->can('Manage Deduction')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $data_monthly = Deduction::where('employee_id', $id)
            ->where('type', 'monthly')
            ->where('month',   date('m'))
            ->where('year',  date('Y'))
            ->get();

        $data_permanent = Deduction::where('employee_id', $id)
            ->where('type', 'permanent')
            ->get();

        $datas = $data_permanent->merge($data_monthly);

        return response()->json([
            'status'    => true,
            'message'   => 'Deductions successfullly retrieved',
            'data'      => $datas
        ], 200);
    }


    public function store(Request $request, $id)
    {
        // dd($id, $request->all());
        if (!Auth::user()->can('Create Deduction')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            // 'employee_id' => 'required|exists:employees,id',
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
        $data['employee_id'] = $id;
        $data['created_by'] = Auth::user()->creatorId();

        // if ($request->type == 'monthly') {
        //     $month = date('m');
        //     $year = date('Y');

        //     $data['month']      = $month;
        //     $data['year']      = $year;
        // }

        $deduction = Deduction::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Deduction created successfully',
            'data' => $deduction
        ], 201);
    }

    public function update(Request $request, $id, $deductionId)
    {
        if (!Auth::user()->can('Edit Deduction')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $deduction = Deduction::find($deductionId);

        if (!$deduction) {
            return response()->json([
                'status' => false,
                'message' => 'deduction not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            // 'employee_id' => 'sometimes|required|exists:employees,id',
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
        // $data['employee_id'] = $id;
        if ($request->type == 'monthly' && $deduction->type != 'monthly') {
            $month = $request->month ?? null;
            $year = $request->year ?? null;

            $data['month']      = $month;
            $data['year']      = $year;
        } else if ($request->type != 'monthly') {
            $data['month']      = null;
            $data['year']      = null;
        }

        $deduction->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Deduction updated successfully',
            'data' => $deduction
        ], 200);
    }

    public function destroy($id, $deductionId)
    {
        if (!Auth::user()->can('Delete Deduction')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $obj = Deduction::find($deductionId);

        if (!$obj) {
            return response()->json([
                'status' => false,
                'message' => 'Deduction not found',
            ], 404);
        }

        $obj->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deduction deleted successfully',
        ], 200);
    }
}
