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
        'rejection_reason'
    ];

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id')->first();
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User', 'approved_by');
    }


    public static $Overtimetype = [
        'fixed' => 'Fixed',
        'percentage' => 'Percentage',
    ];
}
