<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'employee_id',
        'designation_id',
        'promotion_title',
        'promotion_date',
        'description',
        'created_by',
    ];

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'employee_id');
    }
}
