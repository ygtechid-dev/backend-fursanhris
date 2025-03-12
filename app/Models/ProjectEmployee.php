<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectEmployee extends Model
{
    protected $table = 'project_members';

    protected $fillable = [
        'project_id',
        'employee_id',
        // 'role', // 'manager', 'member'
        'assigned_by',
    ];

    // Relasi dengan project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi dengan employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Relasi dengan user/employee yang melakukan assignment
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
