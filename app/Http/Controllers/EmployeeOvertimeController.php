<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use Illuminate\Http\Request;

class EmployeeOvertimeController extends Controller
{
    function getOvertimes($id)
    {
        $datas = Overtime::where('employee_id', $id)
            ->where('status', 'approved')
            ->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Overtimes successfullly retrieved',
            'data'      => $datas
        ], 200);
    }
}
