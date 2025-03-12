<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskEmployee extends Model
{
    protected $table = 'task_employees';

    protected $fillable = [
        'task_id',
        'employee_id',
        'assigned_by',
    ];

    // Relasi dengan task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Relasi dengan employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Relasi dengan employee yang melakukan assignment
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
