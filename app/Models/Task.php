<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', // Menambahkan project_id
        'title',
        'description',
        'status', // 'todo', 'in_progress', 'done'
        'priority', // 'low', 'medium', 'high'
        'due_date',
        'created_by',
        'position',
    ];

    // Relasi dengan project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi dengan employee yang ditugaskan
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees', 'task_id', 'user_id')
            ->withTimestamps();
    }

    // Relasi dengan lampiran/attachment
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    // Relasi dengan komentar
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    // Scope untuk filter berdasarkan status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk tasks yang ditugaskan ke seorang employee
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignees', function ($q) use ($userId) {
            $q->where('task_assignees.user_id', $userId);
        });
    }
}
