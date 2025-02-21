<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Branch')) {

            $branches = Branch::where('created_by', '=', Auth::user()->creatorId())->get();

            return response()->json([
                'status' => true,
                'message' => 'Branch retrieved successfully',
                'data' => $branches
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Branch')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            $branch             = new Branch();
            $branch->name       = $request->name;
            $branch->created_by = Auth::user()->creatorId();
            $branch->save();

            return response()->json([
                'status' => true,
                'message' => 'Branch  successfully created',
                'data' => $branch
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function edit(Branch $branch)
    {
        if (Auth::user()->can('Edit Branch')) {
            if ($branch->created_by == Auth::user()->creatorId()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Branch retrieved successfully',
                    'data' => $branch
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Permission denied.',
                ], 403);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function update(Request $request, Branch $branch)
    {
        if (Auth::user()->can('Edit Branch')) {
            if ($branch->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json([
                        'status'   => false,
                        'message'   => $messages->first()
                    ], 400);
                }

                $branch->name = $request->name;
                $branch->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Branch successfully updated',
                    'data' => $branch
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Permission denied.',
                ], 403);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function destroy(Branch $branch)
    {
        if (Auth::user()->can('Delete Branch')) {
            if ($branch->created_by == Auth::user()->creatorId()) {
                $employee     = Employee::where('branch_id', $branch->id)->get();
                if (count($employee) == 0) {
                    $department = Department::where('branch_id', $branch->id)->first();
                    if (!empty($department)) {
                        $designation = Designation::where('department_id', $department->id)->first();
                    }
                    if (isset($department)) {
                        Designation::where('department_id', $department->branch_id)->delete();
                        $department->delete();
                    }
                    if (isset($designation)) {
                        Designation::where('department_id', $department->branch_id)->delete();
                        $designation->delete();
                    }
                    $branch->delete();
                } else {
                    // return redirect()->route('branch.index')->with('error', __('This branch has employees. Please remove the employee from this branch.'));
                    return response()->json([
                        'status' => false,
                        'message' => 'This branch has employees. Please remove the employee from this branch.',
                    ], 403);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Branch successfully deleted',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Permission denied.',
                ], 403);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
