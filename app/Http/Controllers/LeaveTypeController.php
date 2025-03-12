<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveTypeController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Leave Type')) {
            $leavetypes = LeaveType::where('created_by', '=', Auth::user()->creatorId())->get();

            return response()->json([
                'status' => true,
                'message' => 'Leave types retrieved successfully',
                'data' => $leavetypes
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

        if (Auth::user()->can('Create Leave Type')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'days' => 'required|gt:0',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            $leavetype             = new LeaveType();
            $leavetype->title      = $request->title;
            $leavetype->days       = $request->days;
            $leavetype->created_by = Auth::user()->creatorId();
            $leavetype->save();

            return redirect()->route('leavetype.index')->with('success', __('LeaveType  successfully created.'));

            return response()->json([
                'status' => true,
                'message' => 'LeaveType  successfully created.',
                'data' => [
                    'leave_type' => $leavetype->title,
                ],
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }


    public function update(Request $request, LeaveType $leavetype)
    {
        if (Auth::user()->can('Edit Leave Type')) {
            if ($leavetype->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'title' => 'required',
                        'days' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json([
                        'status'   => false,
                        'message'   => $messages->first()
                    ], 400);
                }

                $leavetype->title = $request->title;
                $leavetype->days  = $request->days;
                $leavetype->save();

                // return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully updated.'));
                return response()->json([
                    'status' => true,
                    'message' => 'LeaveType successfully updated',
                    'data' => [
                        'leave_type' => $leavetype,
                    ],
                ], 201);
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

    public function destroy(LeaveType $leavetype)
    {
        if (Auth::user()->can('Delete Leave Type')) {
            if ($leavetype->created_by == Auth::user()->creatorId()) {
                $leave     = Leave::where('leave_type_id', $leavetype->id)->get();
                if (count($leave) == 0) {
                    $leavetype->delete();
                } else {
                    return redirect()->route('leavetype.index')->with('error', __('This leavetype has leave. Please remove the leave from this leavetype.'));
                }

                return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
