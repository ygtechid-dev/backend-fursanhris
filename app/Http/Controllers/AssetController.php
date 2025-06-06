<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    /**
     * Display a listing of the assets.
     */
    public function index()
    {
        $assets = Asset::with(['employee', 'company'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Office assets retrieved successfully',
            'data' => $assets
        ], 200);
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'warranty_status' => 'required|in:On,Off',
            'buying_date' => 'required|date',
            'image' => 'nullable|string',
            'created_by' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::with('user')->find($request->employee_id);

        if (empty($employee)) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Check if employee already has an asset
        $existingAsset = Asset::where('employee_id', $request->employee_id)->first();
        if ($existingAsset) {
            return response()->json([
                'status' => false,
                'message' => 'Employee already has an asset assigned',
            ], 400);
        }

        $request['created_by'] = Auth::user()->type == 'super admin' ? $request->created_by : $employee?->user->creatorId();
        $asset = Asset::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Asset created successfully',
            'data' => $asset
        ], 201);
    }

    /**
     * Display the specified asset.
     */
    public function show($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'status' => false,
                'message' => 'Asset not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Asset retrieved successfully',
            'data' => $asset
        ], 200);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'status' => false,
                'message' => 'Asset not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'name' => 'string|max:255',
            'brand' => 'string|max:255',
            'warranty_status' => 'in:On,Off',
            'buying_date' => 'date',
            'image' => 'nullable|string',
            'created_by' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::with('user')->find($request->employee_id);
        if (empty($employee)) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        $request['created_by'] = Auth::user()->type == 'super admin' ? $request->created_by : $employee?->user->creatorId();
        $asset->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Asset updated successfully',
            'data' => $asset
        ], 200);
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'status' => false,
                'message' => 'Asset not found'
            ], 404);
        }

        $asset->delete();

        return response()->json([
            'status' => true,
            'message' => 'Asset deleted successfully'
        ], 200);
    }
}
