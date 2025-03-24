<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RewardController extends Controller
{
    /**
     * Display a listing of the rewards.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Rewards')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();

        // Query rewards
        $query = Reward::query();


        $rewards = $query->with(['employee', 'rewardType'])
            ->where('created_by', $user->creatorId())
            ->orderBy('created_at', 'desc')
            ->get();

        // Count rewards by type
        $rewardCounts = DB::table('rewards')
            ->select('reward_type_id', DB::raw('count(*) as count'))
            ->where('created_by', $user->creatorId())
            ->groupBy('reward_type_id')
            ->pluck('count', 'reward_type_id')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Rewards retrieved successfully',
            'data' => [
                'rewards' => $rewards,
                'reward_counts' => $rewardCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created reward in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Manage Rewards')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'reward_type_id' => 'required|exists:reward_types,id',
            'date' => 'required|date',
            'gift' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $request['created_by'] = Auth::user()->creatorId();
        $reward = Reward::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Reward created successfully',
            // 'data' => $reward->load(['employee', 'rewardType']),
        ], 201);
    }

    /**
     * Display the specified reward.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Rewards')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $reward = Reward::with(['employee', 'rewardType'])->find($id);

        if (!$reward) {
            return response()->json([
                'status' => false,
                'message' => 'Reward not found',
            ], 404);
        }

        // Check if user has access to this reward
        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        if ($user->type = 'employee' && $reward->employee_id != $user->employee->id) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Reward retrieved successfully',
            'data' => $reward,
        ], 200);
    }

    /**
     * Update the specified reward in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Rewards')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $reward = Reward::find($id);

        if (!$reward) {
            return response()->json([
                'status' => false,
                'message' => 'Reward not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|required|exists:employees,id',
            'reward_type_id' => 'sometimes|required|exists:reward_types,id',
            'date' => 'sometimes|required|date',
            'gift' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $reward->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Reward updated successfully',
            'data' => $reward->fresh()->load(['employee', 'rewardType']),
        ], 200);
    }

    /**
     * Remove the specified reward from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Manage Rewards')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $reward = Reward::find($id);

        if (!$reward) {
            return response()->json([
                'status' => false,
                'message' => 'Reward not found',
            ], 404);
        }

        $reward->delete();

        return response()->json([
            'status' => true,
            'message' => 'Reward deleted successfully',
        ], 200);
    }
}
