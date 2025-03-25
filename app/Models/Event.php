<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'color',
        'description',
        'start_date',
        'end_date',
        // 'event_type_id',
        'created_by',
        // 'is_all_day',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        // 'is_all_day' => 'boolean',
    ];

    /**
     * The employees assigned to the event
     */
    public function employees()
    {
        return $this->belongsToMany(User::class, 'event_employees')
            // ->withPivot('status')
            ->withTimestamps();
    }
}
