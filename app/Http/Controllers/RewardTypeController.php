<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RewardTypeController extends Controller
{
    /**
     * Display a listing of the reward types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // if (!Auth::user()->can('Manage Reward Types')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Permission denied.',
        //     ], 403);
        // }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();

        // Query reward types
        $query = RewardType::query();
        $rewardTypes = $query
            ->where('created_by', $user->creatorId())
            ->orderBy('created_at', 'desc')->get();

        // Count rewards by type
        $rewardCounts = DB::table('rewards')
            ->where('created_by', $user->creatorId())
            ->select('reward_type_id', DB::raw('count(*) as count'))
            ->groupBy('reward_type_id')
            ->pluck('count', 'reward_type_id')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Reward types retrieved successfully',
            'data' => [
                'reward_types' => $rewardTypes,
                'reward_counts' => $rewardCounts,
            ]
        ], 200);
    }

    /**
     * Store a newly created reward type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // if (!Auth::user()->can('Manage Reward Types')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Permission denied.',
        //     ], 403);
        // }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:reward_types',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rewardType = RewardType::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reward type created successfully',
            'data' => $rewardType,
        ], 201);
    }

    /**
     * Display the specified reward type.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // if (!Auth::user()->can('Manage Reward Types')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Permission denied.',
        //     ], 403);
        // }

        $rewardType = RewardType::find($id);

        if (!$rewardType) {
            return response()->json([
                'status' => false,
                'message' => 'Reward type not found',
            ], 404);
        }

        // Get associated rewards
        $rewards = Reward::where('reward_type_id', $id)
            ->with(['employee'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Reward type retrieved successfully',
            'data' => [
                'reward_type' => $rewardType,
                'rewards' => $rewards,
            ],
        ], 200);
    }

    /**
     * Update the specified reward type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // if (!Auth::user()->can('Manage Reward Types')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Permission denied.',
        //     ], 403);
        // }

        $rewardType = RewardType::find($id);

        if (!$rewardType) {
            return response()->json([
                'status' => false,
                'message' => 'Reward type not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:reward_types,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rewardType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reward type updated successfully',
            'data' => $rewardType,
        ], 200);
    }

    /**
     * Remove the specified reward type from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // if (!Auth::user()->can('Manage Reward Types')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Permission denied.',
        //     ], 403);
        // }

        $rewardType = RewardType::find($id);

        if (!$rewardType) {
            return response()->json([
                'status' => false,
                'message' => 'Reward type not found',
            ], 404);
        }

        // Check if there are any rewards associated with this type
        $rewardsCount = Reward::where('reward_type_id', $id)->count();

        if ($rewardsCount > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete reward type with associated rewards',
                'data' => [
                    'rewards_count' => $rewardsCount
                ]
            ], 422);
        }

        $rewardType->delete();

        return response()->json([
            'status' => true,
            'message' => 'Reward type deleted successfully',
        ], 200);
    }
}
