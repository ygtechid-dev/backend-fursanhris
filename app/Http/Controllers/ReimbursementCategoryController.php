<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReimbursementCategoryController extends Controller
{
    public function index()
    {
        // if (Auth::user()->can('Manage Reimbursement Category')) {
        if (Auth::user()->type == 'super admin') {
            $categories = ReimbursementCategory::get();
        } else {
            $categories = ReimbursementCategory::where('created_by', '=', Auth::user()->creatorId())->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Reimbursement categories retrieved successfully',
            'data' => $categories
        ], 200);
        // } else {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Permission denied.',
        //     ], 403);
        // }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Reimbursement Category')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'max_amount' => 'nullable|numeric|min:0',
                    'is_active' => 'boolean',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status' => false,
                    'message' => $messages->first()
                ], 400);
            }

            $category = new ReimbursementCategory();
            $category->name = $request->name;
            $category->description = $request->description;
            $category->max_amount = $request->max_amount;
            $category->is_active = $request->has('is_active') ? $request->is_active : true;
            $category->created_by = Auth::user()->creatorId();
            $category->save();

            return response()->json([
                'status' => true,
                'message' => 'Reimbursement category successfully created.',
                'data' => [
                    'category' => $category,
                ],
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function show(ReimbursementCategory $category)
    {
        if (Auth::user()->can('View Reimbursement Category')) {
            if ($category->created_by == Auth::user()->creatorId() || Auth::user()->type == 'super admin') {
                return response()->json([
                    'status' => true,
                    'message' => 'Reimbursement category retrieved successfully',
                    'data' => $category
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

    public function update(Request $request, ReimbursementCategory $category)
    {
        if (Auth::user()->can('Edit Reimbursement Category')) {
            if ($category->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:255',
                        'description' => 'nullable|string',
                        'max_amount' => 'nullable|numeric|min:0',
                        'is_active' => 'boolean',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json([
                        'status' => false,
                        'message' => $messages->first()
                    ], 400);
                }

                $category->name = $request->name;
                $category->description = $request->description;
                $category->max_amount = $request->max_amount;
                $category->is_active = $request->has('is_active') ? $request->is_active : $category->is_active;
                $category->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Reimbursement category successfully updated',
                    'data' => [
                        'category' => $category,
                    ],
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

    public function destroy(ReimbursementCategory $category)
    {
        if (Auth::user()->can('Delete Reimbursement Category')) {
            if ($category->created_by == Auth::user()->creatorId()) {
                $reimbursements = Reimbursement::where('category_id', $category->id)->get();

                if (count($reimbursements) == 0) {
                    $category->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Reimbursement category successfully deleted.',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'This category has reimbursements. Please remove the reimbursements from this category first.',
                    ], 400);
                }
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

    public function getActiveCategories()
    {
        if (Auth::user()->can('Manage Reimbursement Category')) {
            if (Auth::user()->type == 'super admin') {
                $categories = ReimbursementCategory::where('is_active', true)->get();
            } else {
                $categories = ReimbursementCategory::where('created_by', '=', Auth::user()->creatorId())
                    ->where('is_active', true)
                    ->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Active reimbursement categories retrieved successfully',
                'data' => $categories
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
