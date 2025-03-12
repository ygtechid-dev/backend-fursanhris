<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'amount',
        'type', // 'permanent' atau 'monthly'
        'month', // untuk type monthly
        'year',  // untuk type monthly
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
