<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
