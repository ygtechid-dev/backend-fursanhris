<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_from',
        'complaint_against',
        'title',
        'complaint_date',
        'description',
        'created_by',
    ];

    /**
     * Mendapatkan karyawan yang mengajukan keluhan
     */
    public function complaintFrom(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'complaint_from');
    }

    /**
     * Mendapatkan karyawan yang dikeluhkan
     */
    public function complaintAgainst(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'complaint_against');
    }
}
