<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $fillable = [
        'task_id',
        'comment',
        'commented_by',
    ];

    // Relasi dengan task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Relasi dengan employee yang membuat komentar
    public function commenter()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
