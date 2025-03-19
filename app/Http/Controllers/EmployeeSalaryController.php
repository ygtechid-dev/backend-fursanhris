<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Set Salary')) {
            $employees = Employee::where(
                [
                    'created_by' => Auth::user()->creatorId(),
                ]
            )->get();

            // Tambahkan net_salary untuk setiap employee
            foreach ($employees as $employee) {
                $employee->net_salary = $employee->calculate_net_salary();
            }

            return response()->json([
                'status' => true,
                'message' => 'Employee Salary retrieved successfully',
                'data' => $employees
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }
}
