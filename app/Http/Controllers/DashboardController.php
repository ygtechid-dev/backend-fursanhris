<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function getCardStats()
    {
        $user = Auth::user();
        if ($user->type == 'company admin' || $user->type == 'company') {
            $currentDate = date('Y-m-d');

            $employees = User::where('type', '=', 'employee')->where('created_by', '=', Auth::user()->creatorId())->get();
            $countEmployee = count($employees);

            $branches = Branch::where('created_by', '=', Auth::user()->creatorId())->get();
            $countBranch = count($branches);

            $notPresentToday    = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');
            $notPresentTodays = Employee::where('created_by', '=', Auth::user()->creatorId())->whereNotIn('id', $notPresentToday)->get();

            $todayAttendance    = AttendanceEmployee::where('date', '=', $currentDate)->get();
            $countTodayAttendance = count($todayAttendance);

            $todayLeave = Leave::where(function ($query) use ($currentDate) {
                $query->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate);
            })->where('status', 'approved')->get();
            $countTodayLeave = count($todayLeave);

            $todayOvertimes = Overtime::where('overtime_date', $currentDate)
                ->where('status', 'approved')
                ->get();
            $countTodayOvertime = count($todayOvertimes);


            return response()->json([
                'status'    => true,
                'data'      => [
                    'countEmployee' => $countEmployee,
                    'countBranch' => $countBranch,
                    'notPresentTodays' => $notPresentTodays,
                    'countTodayAttendance' => $countTodayAttendance,
                    'countTodayLeave' => $countTodayLeave,
                    'countTodayOvertime' => $countTodayOvertime,
                ]
            ]);
        }
    }
}
