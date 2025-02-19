<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceEmployeeController extends Controller
{
    protected $attendance;

    public function __construct(AttendanceEmployee $attendance)
    {
        $this->attendance = $attendance;
    }

    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', Auth::user()->creatorId())->get();

            $department = Department::where('created_by', Auth::user()->creatorId())->get();
            $department->prepend('All', '');

            if (Auth::user()->type == 'employee') {
                $emp = !empty(Auth::user()->employee) ? Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));


                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendanceEmployee = $attendanceEmployee->get();
            } else {
                $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
                $attendanceEmployee = $this->attendance->getAllFilteredAttendance($request, $companyTz);
            }

            return response()->json(
                [
                    'status'    => true,
                    'message' => 'Successfully retrieved data.',
                    'data'      => [
                        'attendanceEmployee' => $attendanceEmployee,
                        'branch' => $branch,
                        'department' => $department,
                    ]
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'status'    => false,
                    'message' => __('Permission denied.')
                ],
                403
            );
        }
    }
}
