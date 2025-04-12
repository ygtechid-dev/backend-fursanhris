<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Resignation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResignationController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            // 'employee_id' => 'required|exists:employees,id',
            'notice_date' => 'nullable|date',
            'resignation_date' => 'required|date',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::with('employee')->find(Auth::user()->id);

        if ($user && $user->employee) {
            $existingResignation = Resignation::where('employee_id', $user->employee->id)
                ->where(function ($query) {
                    // Consider a resignation active if it's in the future
                    $query->where('resignation_date', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->first();

            if ($existingResignation) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already have an active resignation request.',
                    'data' => $existingResignation
                ], 422);
            }
        }

        $resignation = Resignation::create([
            'employee_id' => $user?->employee?->id,
            'notice_date' => Carbon::now()->format('Y-m-d'),
            'resignation_date' => $request->resignation_date,
            'description' => $request->description,
            'created_by' => $user->creatorId(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Resignation submited successfully',
            'data' => $resignation
        ], 201);
    }
}
