<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Designation')) {
            if (Auth::user()->type == 'super admin') {
                $designations = Designation::with(['company', 'department.branch']);
            } else {
                $designations = Designation::query()->where('created_by', '=', Auth::user()->creatorId())->with('department.branch');
            }

            if ($request->has('branch_id')) {
                $designations->where('branch_id', $request->branch_id);
            }

            if ($request->has('department_id')) {
                $designations->where('department_id', $request->department_id);
            }

            return response()->json([
                'status' => true,
                'message' => 'User retrieved successfully',
                'data' => $designations->get()
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
        if (Auth::user()->can('Create Designation')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'department_id' => 'required',
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

            try {
                $branch = Department::where('id', $request->department_id)->when(Auth::user()->type != 'super admin', function ($q) {
                    $q->where('created_by', Auth::user()->creatorId());
                })->first()->branch->id;
            } catch (Exception $e) {
                $branch = null;
            }

            $designation                = new Designation();
            $designation->branch_id     = $branch;
            $designation->department_id = $request->department_id;
            $designation->name          = $request->name;
            $designation->created_by    = Auth::user()->type == 'super admin' ? $request->created_by :  Auth::user()->creatorId();

            $designation->save();


            return response()->json([
                'status' => true,
                'message' => 'Designation  successfully created.',
                'data' => $designation,
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function update(Request $request, Designation $designation)
    {
        if (Auth::user()->can('Edit Designation')) {
            // if ($designation->created_by == Auth::user()->creatorId()) {
            $validator = Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'department_id' => 'required',
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

            try {
                $branch = Department::where('id', $request->department_id)->when(Auth::user()->type != 'super admin', function ($q) {
                    $q->where('created_by', Auth::user()->creatorId());
                })->first()->branch->id;
            } catch (Exception $e) {
                $branch = null;
            }

            $designation->name          = $request->name;
            $designation->branch_id     = $branch;
            $designation->department_id = $request->department_id;
            $designation->created_by = Auth::user()->type == 'super admin' ? $request->created_by :  Auth::user()->creatorId();
            $designation->save();

            return response()->json([
                'status' => true,
                'message' => 'Designation  successfully updated.',
                'data' => $designation,
            ], 201);
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

    public function destroy(Designation $designation)
    {
        if (Auth::user()->can('Delete Designation')) {
            $employee     = Employee::where('designation_id', $designation->id)->get();
            if (count($employee) == 0) {
                if ($designation->created_by == Auth::user()->creatorId()) {
                    $designation->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Designation successfully deleted.',
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
                    'message' => 'This designation has employees. Please remove the employee from this designation.',
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
