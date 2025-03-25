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

    protected $hidden = [
        'password'
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
        return $this->belongsTo('App\Models\Branch', 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo('App\Models\Designation', 'designation_id');
    }

    public function salary_type()
    {
        return $this->salary_type == 'monthly' ? 'Monthly Payslip' : 'Hourly Payslip';
    }

    public function calculate_net_salary($month = null, $year = null)
    {
        $employee_id = $this->id;
        if (empty($month)) {
            $month = date('m');
        }

        if (empty($year)) {
            $year = date('Y');
        }

        $employee = Employee::with('user')->find($employee_id);
        $basic_salary = $employee->salary;

        // Calculate Allowances (tunjangan)
        $totalAllowance = 0;

        // Allowance permanen
        $permanentAllowances = Allowance::where('employee_id', $employee_id)
            ->where('type', 'permanent')
            ->where('created_by', $employee?->user?->creatorId())
            ->get();

        // Allowance bulanan
        $monthlyAllowances = Allowance::where('employee_id', $employee_id)
            ->where('type', 'monthly')
            ->where('month', $month)
            ->where('year', $year)
            ->where('created_by', $employee?->user?->creatorId())
            ->get();

        $allowances = $permanentAllowances->merge($monthlyAllowances);

        // Menghitung total allowance
        foreach ($allowances as $allowance) {
            $totalAllowance += $allowance->amount;
        }

        // Calculate Deductions (potongan)
        $totalDeduction = 0;

        // Deduction permanen
        $permanentDeductions = Deduction::where('employee_id', $employee_id)
            ->where('type', 'permanent')
            ->where('created_by', $employee?->user?->creatorId())
            ->get();

        // Deduction bulanan
        $monthlyDeductions = Deduction::where('employee_id', $employee_id)
            ->where('type', 'monthly')
            ->where('month', $month)
            ->where('year', $year)
            ->where('created_by', $employee?->user?->creatorId())
            ->get();

        $deductions = $permanentDeductions->merge($monthlyDeductions);

        // Menghitung total deduction
        foreach ($deductions as $deduction) {
            $totalDeduction += $deduction->amount;
        }

        // Calculate Overtime
        $overtimeTotal = 0;
        $approvedOvertimes = Overtime::where('employee_id', $employee_id)
            ->whereMonth('overtime_date', $month)
            ->whereYear('overtime_date', $year)
            ->where('status', 'approved')
            ->get();

        foreach ($approvedOvertimes as $overtime) {
            $total_work = $overtime->number_of_days * $overtime->hours;
            $overtimeAmount = $total_work * $overtime->rate;
            $overtimeTotal += $overtimeAmount;
        }
        // dd($basic_salary, $totalAllowance, $overtimeTotal, $totalDeduction);
        // Calculate Net Salary
        $net_salary = $basic_salary + $totalAllowance + $overtimeTotal - $totalDeduction;

        return $net_salary;
    }
}
