<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Department')) {

            $branches = Department::query()->where('created_by', '=', Auth::user()->creatorId())->with('branch');

            if ($request->has('branch_id')) {
                $branches->where('branch_id', $request->branch_id);
            }

            return response()->json([
                'status' => true,
                'message' => 'Department retrieved successfully',
                'data' => $branches->get()
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
