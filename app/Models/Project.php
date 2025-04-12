<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status', // 'planning', 'active', 'on_hold', 'completed'
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi dengan tasks
    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->withPivot('assigned_by')  // Add assigned_by to the pivot fields
            ->withTimestamps();
    }

    // Mendapatkan tasks berdasarkan status
    public function tasksByStatus($status)
    {
        return $this->tasks()->where('status', $status)->get();
    }
}
