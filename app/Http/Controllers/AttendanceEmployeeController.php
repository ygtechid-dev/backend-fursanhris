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

            if (Auth::user()->type == 'company') {
                // $emp = !empty(Auth::user()->employee) ? Auth::user()->employee->id : 0;

                // $attendanceEmployee = AttendanceEmployee::where('created_by', Auth::user()->creatorId());

                // if ($request->type == 'monthly' && !empty($request->month)) {
                //     $month = date('m', strtotime($request->month));
                //     $year  = date('Y', strtotime($request->month));


                //     $start_date = date($year . '-' . $month . '-01');
                //     $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                //     // old date
                //     // $end_date   = date($year . '-' . $month . '-t');

                //     $attendanceEmployee->whereBetween(
                //         'date',
                //         [
                //             $start_date,
                //             $end_date,
                //         ]
                //     );
                // } elseif ($request->type == 'daily' && !empty($request->date)) {
                //     $attendanceEmployee->where('date', $request->date);
                // } else {
                //     $month      = date('m');
                //     $year       = date('Y');
                //     $start_date = date($year . '-' . $month . '-01');
                //     $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                //     // old date
                //     // $end_date   = date($year . '-' . $month . '-t');

                //     $attendanceEmployee->whereBetween(
                //         'date',
                //         [
                //             $start_date,
                //             $end_date,
                //         ]
                //     );
                // }

                // $attendanceEmployee = $attendanceEmployee->get();

                $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
                $attendanceEmployee = $this->attendance->getAllFilteredAttendance($request, $companyTz);
            } else {
                // $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
                $attendanceEmployee = $this->attendance->getAllFilteredAttendance($request);
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

    /**
     * Display the specified attendance record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Auth::user()->can('Manage Attendance')) {
            // Get company timezone
            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            // Find the attendance record
            $attendance = AttendanceEmployee::with('employee')->find($id);

            if (!$attendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Attendance record not found.'
                ], 404);
            }

            // Check permission - user can only view their own records or any if admin
            $canView = false;
            if (Auth::user()->type == 'employee') {
                $emp = !empty(Auth::user()->employee) ? Auth::user()->employee->id : 0;
                $canView = ($attendance->employee_id == $emp);
            } else {
                $canView = true;
            }

            if (!$canView) {
                return response()->json([
                    'status' => false,
                    'message' => __('Permission denied.')
                ], 403);
            }

            // Format the times with timezone
            $clockIn = $attendance->clock_in && $attendance->clock_in != '00:00:00'
                ? \Carbon\Carbon::parse($attendance->clock_in, 'UTC')
                ->setDate(
                    \Carbon\Carbon::parse($attendance->date)->year,
                    \Carbon\Carbon::parse($attendance->date)->month,
                    \Carbon\Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            $clockOut = $attendance->clock_out && $attendance->clock_out != '00:00:00'
                ? \Carbon\Carbon::parse($attendance->clock_out, 'UTC')
                ->setDate(
                    \Carbon\Carbon::parse($attendance->date)->year,
                    \Carbon\Carbon::parse($attendance->date)->month,
                    \Carbon\Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            $attendanceData = [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'employee_id' => $attendance->employee_id,
                'employee_name' => $attendance->employee->name ?? 'N/A',
                'status' => $attendance->status,
                'clock_in' => $clockIn ? $clockIn->format('H:i:s') : null,
                'clock_in_formatted' => $clockIn ? $clockIn->format('Y-m-d H:i:s') : null,
                'clock_in_location' => $attendance->clock_in_location,
                'clock_in_latitude' => $attendance->clock_in_latitude,
                'clock_in_longitude' => $attendance->clock_in_longitude,
                'clock_in_photo' => $attendance->clock_in_photo,
                'clock_in_notes' => $attendance->clock_in_notes,
                'clock_out' => $clockOut ? $clockOut->format('H:i:s') : null,
                'clock_out_formatted' => $clockOut ? $clockOut->format('Y-m-d H:i:s') : null,
                'clock_out_location' => $attendance->clock_out_location,
                'clock_out_latitude' => $attendance->clock_out_latitude,
                'clock_out_longitude' => $attendance->clock_out_longitude,
                'clock_out_photo' => $attendance->clock_out_photo,
                'clock_out_notes' => $attendance->clock_out_notes,
                'late' => $attendance->late,
                'early_leaving' => $attendance->early_leaving,
                'overtime' => $attendance->overtime,
                'total_rest' => $attendance->total_rest,
                'timezone' => $attendance->timezone,
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
                'created_by' => $attendance->created_by,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved attendance details.',
                'data' => $attendanceData
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }
}
