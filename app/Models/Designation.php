<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'branch_id',
        'department_id',
        'name',
        'created_by'
    ];

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch', 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
