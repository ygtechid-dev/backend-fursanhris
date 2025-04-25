<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'number_of_days',
        'overtime_date',
        'start_time',
        'end_time',
        'hours',
        'rate',
        'remark',
        'created_by',

        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason'
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }


    public static $Overtimetype = [
        'fixed' => 'Fixed',
        'percentage' => 'Percentage',
    ];

    /**
     * Menghitung total jam lembur untuk satu karyawan
     * 
     * @param int $employee_id
     * @param string|null $month Format: '01', '02', ..., '12'
     * @param string|null $year Format: 'YYYY'
     * @param string|null $start_date Format: 'Y-m-d'
     * @param string|null $end_date Format: 'Y-m-d'
     * @return float
     */
    public static function calculateEmployeeWorkHours($employee_id, $month = null, $year = null, $start_date = null, $end_date = null)
    {
        $query = self::where('employee_id', $employee_id)
            ->where('status', 'approved');

        // Filter berdasarkan bulan dan tahun
        if ($month && $year) {
            $query->whereMonth('overtime_date', $month)
                ->whereYear('overtime_date', $year);
        } elseif ($year) {
            $query->whereYear('overtime_date', $year);
        }

        // Filter berdasarkan rentang tanggal jika diisi
        if ($start_date && $end_date) {
            $query->whereBetween('overtime_date', [$start_date, $end_date]);
        }

        $overtimes = $query->get();
        $totalHours = 0;

        foreach ($overtimes as $overtime) {
            $totalHours += ($overtime->number_of_days * $overtime->hours);
        }

        return $totalHours;
    }
}
