<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'branch_id',
        'created_by',
    ];


    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
