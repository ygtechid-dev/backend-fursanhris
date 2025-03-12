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
}
