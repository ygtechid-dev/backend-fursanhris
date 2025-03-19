<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AttendanceEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'timezone',
        'created_by',
        // New fields for clock in
        'clock_in_location',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_in_photo',
        'clock_in_notes',
        // New fields for clock out
        'clock_out_location',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_out_photo',
        'clock_out_notes'
    ];

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }

    public function getAllFilteredAttendance($request, $companyTz = 'UTC')
    {
        // Start with base query for non-employee users
        $query = $this->with('employee');

        // Get employee IDs based on filters
        $employeeQuery = Employee::select('id')
            ->where('created_by', Auth::user()->creatorId());

        if (!empty($request->branch)) {
            $employeeQuery->where('branch_id', $request->branch);
        }

        if (!empty($request->department)) {
            $employeeQuery->where('department_id', $request->department);
        }

        $employeeIds = $employeeQuery->pluck('id');
        $query->whereIn('employee_id', $employeeIds);

        // Apply date filters
        if ($request->type == 'monthly' && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year = date('Y', strtotime($request->month));
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
        } elseif ($request->type == 'daily' && !empty($request->date)) {
            $start_date = $request->date;
            $end_date = $request->date;
        } else {
            $month = date('m');
            $year = date('Y');
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
        }

        $query->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc');

        // Transform the results with timezone handling
        return $query->get()->map(function ($attendance) use ($companyTz) {
            // Convert UTC times to company timezone
            $clockIn = $attendance->clock_in && $attendance->clock_in != '00:00:00'
                ? Carbon::parse($attendance->clock_in, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            $clockOut = $attendance->clock_out && $attendance->clock_out != '00:00:00'
                ? Carbon::parse($attendance->clock_out, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            return [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'employee_name' => $attendance->employee->name ?? 'N/A',
                'clock_in' => $clockIn ? $clockIn->format('H:i:s') : null,
                'clock_in_location' => $attendance->clock_in_location,
                'clock_in_latitude' => $attendance->clock_in_latitude,
                'clock_in_longitude' => $attendance->clock_in_longitude,
                'clock_in_photo' => $attendance->clock_in_photo,
                'clock_in_notes' => $attendance->clock_in_notes,
                'clock_out' => $clockOut ? $clockOut->format('H:i:s') : null,
                'clock_out_location' => $attendance->clock_out_location,
                'clock_out_latitude' => $attendance->clock_out_latitude,
                'clock_out_longitude' => $attendance->clock_out_longitude,
                'clock_out_photo' => $attendance->clock_out_photo,
                'clock_out_notes' => $attendance->clock_out_notes,
                'status' => $attendance->status,
                'late' => $attendance->late,
                'early_leaving' => $attendance->early_leaving,
                'timezone' => $attendance->timezone,
                'overtime' => $attendance->overtime,
                'total_rest' => $attendance->total_rest,
                'clock_in_formatted' => $clockIn ? $clockIn->format('Y-m-d H:i:s') : null,
                'clock_out_formatted' => $clockOut ? $clockOut->format('Y-m-d H:i:s') : null,
            ];
        });
    }

    public function getEmployeeAttendanceHistory($employee_id, $start_date = null, $end_date = null, $companyTz = 'UTC')
    {
        $query = $this->where('employee_id', $employee_id)
            ->with('employee')
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc');

        if ($start_date && $end_date) {
            // Convert date range to company timezone for querying
            $startDate = Carbon::parse($start_date, $companyTz)->startOfDay();
            $endDate = Carbon::parse($end_date, $companyTz)->endOfDay();

            $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }

        return $query->get()->map(function ($attendance) use ($companyTz) {
            // Convert UTC times to company timezone
            $clockIn = $attendance->clock_in && $attendance->clock_in != '00:00:00'
                ? Carbon::parse($attendance->clock_in, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            $clockOut = $attendance->clock_out && $attendance->clock_out != '00:00:00'
                ? Carbon::parse($attendance->clock_out, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            return [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'employee_name' => $attendance->employee->name ?? 'N/A',
                'clock_in' => $clockIn ? $clockIn->format('H:i:s') : null,
                'clock_in_location' => $attendance->clock_in_location,
                'clock_in_latitude' => $attendance->clock_in_latitude,
                'clock_in_longitude' => $attendance->clock_in_longitude,
                'clock_in_photo' => $attendance->clock_in_photo,
                'clock_in_notes' => $attendance->clock_in_notes,
                'clock_out' => $clockOut ? $clockOut->format('H:i:s') : null,
                'clock_out_location' => $attendance->clock_out_location,
                'clock_out_latitude' => $attendance->clock_out_latitude,
                'clock_out_longitude' => $attendance->clock_out_longitude,
                'clock_out_photo' => $attendance->clock_out_photo,
                'clock_out_notes' => $attendance->clock_out_notes,
                'status' => $attendance->status,
                'late' => $attendance->late,
                'early_leaving' => $attendance->early_leaving,
                'timezone' => $attendance->timezone,
                'overtime' => $attendance->overtime,
                'total_rest' => $attendance->total_rest,
                // Add formatted timestamps for frontend display
                'clock_in_formatted' => $clockIn ? $clockIn->format('Y-m-d H:i:s') : null,
                'clock_out_formatted' => $clockOut ? $clockOut->format('Y-m-d H:i:s') : null,
            ];
        });
    }

    /**
     * Calculate total working hours for an employee within a specific month
     *
     * @param int $employee_id
     * @param string $year_month Format: 'YYYY-MM'
     * @param string $companyTz Company timezone
     * @return array
     */
    public function calculateMonthlyWorkingPeriod($employee_id, $year_month, $companyTz = 'UTC')
    {
        // Parse the year and month
        $year = date('Y', strtotime($year_month));
        $month = date('m', strtotime($year_month));

        // Start date and end date for the month
        $start_date = date($year . '-' . $month . '-01');
        $end_date = date('Y-m-t', strtotime($start_date));

        // Get all attendance records for the employee within the date range
        $attendances = $this->where('employee_id', $employee_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->get();

        // Initialize counters
        $totalWorkingMinutes = 0;
        $totalWorkingDays = 0;
        $totalLateMinutes = 0;
        $totalEarlyLeaveMinutes = 0;
        $totalOvertimeMinutes = 0;
        $totalRestMinutes = 0;
        $daysPresent = 0;
        $daysAbsent = 0;

        // Process each attendance record
        foreach ($attendances as $attendance) {
            // Skip if status is 'Absent'
            if ($attendance->status == 'Absent') {
                $daysAbsent++;
                continue;
            }

            $daysPresent++;

            // Skip if no clock in or clock out
            if (
                !$attendance->clock_in || !$attendance->clock_out ||
                $attendance->clock_in == '00:00:00' || $attendance->clock_out == '00:00:00'
            ) {
                continue;
            }

            // Convert clock times to Carbon instances with the date included
            $clockIn = Carbon::parse($attendance->clock_in, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz);

            $clockOut = Carbon::parse($attendance->clock_out, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz);

            // Handle overnight shifts (if clock out is earlier than clock in, assume it's the next day)
            if ($clockOut->lt($clockIn)) {
                $clockOut->addDay();
            }

            // Calculate work duration in minutes
            $workDuration = $clockIn->diffInMinutes($clockOut, false);

            if ($attendance->total_rest && is_numeric($attendance->total_rest) && $attendance->total_rest > 0) {
                $workDuration -= (float)$attendance->total_rest;
                $totalRestMinutes += (float)$attendance->total_rest;
            }

            // Add to totals
            $totalWorkingMinutes += $workDuration;
            $totalWorkingDays++;

            if ($attendance->late && is_numeric($attendance->late) && $attendance->late > 0) {
                $totalLateMinutes += (float)$attendance->late;
            }

            // Similarly for other fields:
            if ($attendance->early_leaving && is_numeric($attendance->early_leaving) && $attendance->early_leaving > 0) {
                $totalEarlyLeaveMinutes += (float)$attendance->early_leaving;
            }

            if ($attendance->overtime && is_numeric($attendance->overtime) && $attendance->overtime > 0) {
                $totalOvertimeMinutes += (float)$attendance->overtime;
            }
        }

        // Calculate hours from minutes
        $totalWorkingHours = round($totalWorkingMinutes / 60, 2);
        $totalLateHours = round($totalLateMinutes / 60, 2);
        $totalEarlyLeaveHours = round($totalEarlyLeaveMinutes / 60, 2);
        $totalOvertimeHours = round($totalOvertimeMinutes / 60, 2);
        $totalRestHours = round($totalRestMinutes / 60, 2);

        // Get month name
        $monthName = date('F', strtotime($year_month));

        // Return summary
        return [
            'employee_id' => $employee_id,
            'year' => $year,
            'month' => $month,
            'month_name' => $monthName,
            'year_month' => $year_month,
            'total_working_days' => $totalWorkingDays,
            'days_present' => $daysPresent,
            'days_absent' => $daysAbsent,
            'total_working_minutes' => $totalWorkingMinutes,
            'total_working_hours' => $totalWorkingHours,
            'total_late_minutes' => $totalLateMinutes,
            'total_late_hours' => $totalLateHours,
            'total_early_leave_minutes' => $totalEarlyLeaveMinutes,
            'total_early_leave_hours' => $totalEarlyLeaveHours,
            'total_overtime_minutes' => $totalOvertimeMinutes,
            'total_overtime_hours' => $totalOvertimeHours,
            'total_rest_minutes' => $totalRestMinutes,
            'total_rest_hours' => $totalRestHours,
            'average_hours_per_day' => $totalWorkingDays > 0 ? round($totalWorkingHours / $totalWorkingDays, 2) : 0,
        ];
    }

    /**
     * Calculate working period for a single employee across multiple months
     *
     * @param int $employee_id
     * @param string $start_year_month Format: 'YYYY-MM'
     * @param string $end_year_month Format: 'YYYY-MM'
     * @param string $companyTz Company timezone
     * @return array
     */
    public function calculateEmployeeWorkingPeriods($employee_id, $start_year_month, $end_year_month, $companyTz = 'UTC')
    {
        // Parse start and end dates
        $startDate = Carbon::parse($start_year_month . '-01');
        $endDate = Carbon::parse($end_year_month . '-01')->endOfMonth();

        // Calculate difference in months
        $diffInMonths = $startDate->diffInMonths($endDate) + 1;

        $results = [];
        $currentDate = clone $startDate;

        // Get employee details
        $employee = Employee::find($employee_id);
        $employeeName = $employee ? $employee->name : 'Unknown';

        // For each month, calculate working period
        for ($i = 0; $i < $diffInMonths; $i++) {
            $year_month = $currentDate->format('Y-m');
            $monthData = $this->calculateMonthlyWorkingPeriod($employee_id, $year_month, $companyTz);

            // Add employee name
            $monthData['employee_name'] = $employeeName;

            $results[] = $monthData;
            $currentDate->addMonth();
        }

        // Calculate totals across all months
        $totals = [
            'employee_id' => $employee_id,
            'employee_name' => $employeeName,
            'start_year_month' => $start_year_month,
            'end_year_month' => $end_year_month,
            'total_months' => count($results),
            'total_working_days' => 0,
            'total_days_present' => 0,
            'total_days_absent' => 0,
            'total_working_hours' => 0,
            'total_late_hours' => 0,
            'total_early_leave_hours' => 0,
            'total_overtime_hours' => 0,
            'total_rest_hours' => 0,
            'average_working_hours_per_month' => 0,
        ];

        foreach ($results as $monthData) {
            $totals['total_working_days'] += $monthData['total_working_days'];
            $totals['total_days_present'] += $monthData['days_present'];
            $totals['total_days_absent'] += $monthData['days_absent'];
            $totals['total_working_hours'] += $monthData['total_working_hours'];
            $totals['total_late_hours'] += $monthData['total_late_hours'];
            $totals['total_early_leave_hours'] += $monthData['total_early_leave_hours'];
            $totals['total_overtime_hours'] += $monthData['total_overtime_hours'];
            $totals['total_rest_hours'] += $monthData['total_rest_hours'];
        }

        // Calculate averages
        if (count($results) > 0) {
            $totals['average_working_hours_per_month'] = round($totals['total_working_hours'] / count($results), 2);
        }

        return [
            'monthly_data' => $results,
            'summary' => $totals
        ];
    }

    /**
     * Get annual working period summary for a specific employee
     *
     * @param int $employee_id
     * @param int $year
     * @param string $companyTz Company timezone
     * @return array
     */
    public function getAnnualWorkingPeriodSummary($employee_id, $year, $companyTz = 'UTC')
    {
        $start_year_month = $year . '-01';  // January
        $end_year_month = $year . '-12';    // December

        return $this->calculateEmployeeWorkingPeriods($employee_id, $start_year_month, $end_year_month, $companyTz);
    }

    /**
     * Get custom date range working period summary for a specific employee
     *
     * @param int $employee_id
     * @param string $start_date Format: 'YYYY-MM-DD'
     * @param string $end_date Format: 'YYYY-MM-DD'
     * @param string $companyTz Company timezone
     * @return array
     */
    public function getCustomRangeWorkingPeriod($employee_id, $start_date, $end_date, $companyTz = 'UTC')
    {
        // Convert to Carbon instances for easier manipulation
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);

        // Format to year-month for our method
        $start_year_month = $startDate->format('Y-m');
        $end_year_month = $endDate->format('Y-m');

        return $this->calculateEmployeeWorkingPeriods($employee_id, $start_year_month, $end_year_month, $companyTz);
    }
}
