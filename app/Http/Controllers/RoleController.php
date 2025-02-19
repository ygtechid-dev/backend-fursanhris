<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Role')) {
            $roles = Role::where('created_by', '=', Auth::user()->creatorId())->get();

            return response()->json([
                'status'    => true,
                'message'   => 'Successfully retrieved data',
                'data'      => $roles
            ]);
        } else {
            return response()->json([
                'status'    => true,
                'message' => __('Permission denied.')
            ], 403);
        }
    }
}
