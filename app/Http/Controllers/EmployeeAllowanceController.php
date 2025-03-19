<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeAllowanceController extends Controller
{
    function getAllowances($id)
    {
        if (!Auth::user()->can('Manage Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $data_monthly = Allowance::where('employee_id', $id)
            ->where('type', 'monthly')
            ->where('month',   date('m'))
            ->where('year',  date('Y'))
            ->get();

        $data_permanent = Allowance::where('employee_id', $id)
            ->where('type', 'permanent')
            ->get();

        $datas = $data_permanent->merge($data_monthly);

        return response()->json([
            'status'    => true,
            'message'   => 'Allowances successfullly retrieved',
            'data'      => $datas
        ], 200);
    }

    public function store(Request $request, $id)
    {
        // dd($id, $request->all());
        if (!Auth::user()->can('Create Allowance')) {
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

        $allowance = Allowance::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Allowance created successfully',
            'data' => $allowance
        ], 201);
    }

    public function update(Request $request, $id, $allowanceId)
    {
        if (!Auth::user()->can('Edit Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $allowance = Allowance::find($allowanceId);

        if (!$allowance) {
            return response()->json([
                'status' => false,
                'message' => 'Allowance not found',
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
        if ($request->type == 'monthly' && $allowance->type != 'monthly') {
            $month = $request->month ?? null;
            $year = $request->year ?? null;

            $data['month']      = $month;
            $data['year']      = $year;
        } else if ($request->type != 'monthly') {
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

    public function destroy($id, $allowanceId)
    {
        if (!Auth::user()->can('Delete Allowance')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $obj = Allowance::find($allowanceId);

        if (!$obj) {
            return response()->json([
                'status' => false,
                'message' => 'Allowance not found',
            ], 404);
        }

        $obj->delete();

        return response()->json([
            'status' => true,
            'message' => 'Allowance deleted successfully',
        ], 200);
    }
}
