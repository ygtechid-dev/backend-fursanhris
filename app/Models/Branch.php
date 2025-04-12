<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'created_by'
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
