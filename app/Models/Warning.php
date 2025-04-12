<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    protected $fillable = [
        'warning_to',
        'warning_by',
        'subject',
        'warning_date',
        'description',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke pegawai yang diberi peringatan
    public function warningTo()
    {
        return $this->belongsTo(Employee::class, 'warning_to');
    }

    // Relasi ke pegawai yang memberi peringatan
    public function warningBy()
    {
        return $this->belongsTo(Employee::class, 'warning_by');
    }
}
