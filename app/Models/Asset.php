<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'brand',
        'warranty_status',
        'buying_date',
        'image',
        'created_by',
    ];

    protected $casts = [
        'buying_date' => 'date',
    ];

    public static function getEmployeeAsset($employee_id)
    {
        return self::where('employee_id', $employee_id)->get();
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }
}
