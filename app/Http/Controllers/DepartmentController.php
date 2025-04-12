<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Department')) {
            $user = Auth::user();
            if (Auth::user()->type == 'super admin') {
                $branches = Department::with(['company', 'branch']);
            } else {
                $branches = Department::query()->where('created_by', '=', Auth::user()->creatorId())->with('branch');
            }


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

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Department')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'name' => 'required|max:20',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            $department             = new Department();
            $department->branch_id  = $request->branch_id;
            $department->name       = $request->name;
            $department->created_by = Auth::user()->type == 'super admin' ? $request->created_by :  Auth::user()->creatorId();
            $department->save();

            return response()->json([
                'status' => true,
                'message' => 'Department  successfully created',
                'data' => $department
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function edit(Department $department)
    {
        if (Auth::user()->can('Edit Department')) {
            // if ($department->created_by == Auth::user()->creatorId()) {
            $branch = Branch::where('created_by', Auth::user()->creatorId())->get();

            return response()->json([
                'status' => true,
                'message' => 'Department retrieved successfully',
                'data' => [
                    'department'    => $department,
                    'branch'    => $branch,
                ]
            ], 200);
            // } else {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Permission denied.',
            //     ], 403);
            // }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function update(Request $request, Department $department)
    {
        if (Auth::user()->can('Edit Department')) {
            // if ($department->created_by == Auth::user()->creatorId()) {
            $validator = Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'name' => 'required|max:20',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            $department->branch_id = $request->branch_id;
            $department->name      = $request->name;
            $department->created_by = Auth::user()->type == 'super admin' ? $request->created_by :  Auth::user()->creatorId();
            $department->save();

            return response()->json([
                'status' => true,
                'message' => 'Department successfully updated.',
            ], 200);
            // } else {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Permission denied.',
            //     ], 403);
            // }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function destroy(Department $department)
    {
        if (Auth::user()->can('Delete Department')) {
            if ($department->created_by == Auth::user()->creatorId()) {
                $employee     = Employee::where('department_id', $department->id)->get();
                if (count($employee) == 0) {
                    Designation::where('department_id', $department->id)->delete();
                    $department->delete();
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'This department has employees. Please remove the employee from this department.',
                    ], 403);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Department successfully deleted.',
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
