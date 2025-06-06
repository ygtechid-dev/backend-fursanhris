<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        $asset = Asset::getEmployeeAsset($user?->employee?->id);

        return response()->json([
            'status' => true,
            'message' => 'Employee asset retrieved successfully',
            'data' =>  $asset
        ], 200);
    }
}
