<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'biometric_emp_id',
        'branch_id',
        'department_id',
        'designation_id',
        'company_doj',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'tax_payer_id',
        'salary_type',
        'account_type',
        'salary',
        'created_by',
    ];

    public function documents()
    {
        return $this->hasMany('App\Models\EmployeeDocument', 'employee_id', 'employee_id')->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function employee_name($name)
    {

        $employee = Employee::where('id', $name)->first();
        if (!empty($employee)) {
            $first_name = $employee->first_name;
            $last_name = $employee->last_name;

            return "$first_name $last_name";
        }
    }


    public static function login_user($name)
    {
        $user = User::where('id', $name)->first();
        $first_name = $user->first_name;
        $last_name = $user->last_name;
        return "$first_name $last_name";
    }

    public static function employee_salary($salary)
    {

        $employee = Employee::where("salary", $salary)->first();
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch', 'id', 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'id', 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo('App\Models\Designation', 'id', 'designation_id');
    }
}
