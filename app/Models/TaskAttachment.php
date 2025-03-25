<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'url',
    ];

    // Relasi dengan task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Relasi dengan uploader
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function setUrlAttribute($value)
    {
        // Validate URL format if needed
        $this->attributes['url'] = filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }
}
