<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Payslip extends Model
{
    protected $fillable = [
        'employee_id',
        'payslip_number',
        'month',
        'year',
        'salary_type', // monthly, hourly
        'basic_salary',
        'total_allowance',
        'total_deduction',
        'total_overtime',
        'allowance',
        'deduction',
        'overtime',
        'net_salary',
        'payment_status', // paid, unpaid
        'payment_date',
        'payment_method', // bank transfer, cash, etc
        'note',
        'file_url',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi dengan employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getAllFilteredPayslips($request)
    {
        // Base query
        $query = $this->with(['employee', 'employee.branch', 'employee.department']);

        // Filter berdasarkan karyawan
        $employeeQuery = Employee::select('id')
            ->where('created_by', Auth::user()->creatorId());

        if (!empty($request->branch)) {
            $employeeQuery->where('branch_id', $request->branch);
        }

        if (!empty($request->department)) {
            $employeeQuery->where('department_id', $request->department);
        }

        if (!empty($request->employee)) {
            $employeeQuery->where('id', $request->employee);
        }

        $employeeIds = $employeeQuery->pluck('id');
        $query->whereIn('employee_id', $employeeIds);

        // Filter berdasarkan bulan dan tahun
        if (!empty($request->month) && !empty($request->year)) {
            $month = $request->month;
            $year = $request->year;
            $query->where('month', $month)->where('year', $year);
        } else {
            $month = date('m');
            $year = date('Y');
            $query->where('month', $month)->where('year', $year);
        }

        // Filter berdasarkan status pembayaran
        if (!empty($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }

        $payslips = $query->orderBy('created_at', 'desc')->get();

        // Calculate net salary for each payslip if not already calculated
        foreach ($payslips as $payslip) {
            if (!$payslip->net_salary) {
                $payslip->net_salary = $this->calculateNetSalary($payslip->employee_id, $payslip->month, $payslip->year);
                $payslip->save();
            }
        }

        return $payslips;
    }

    // Generate Payslip untuk satu bulan
    public static function generatePayslip($month, $year, $created_by = null)
    {
        // if ($created_by == null) {
        //     $created_by = Auth::user()->creatorId();
        // }

        if (Auth::user()->type == 'super admin') {
            $employees = Employee::get();
        } else {
            $employees = Employee::where('created_by', $created_by)->get();
        }
        $payslips = [];

        foreach ($employees as $employee) {
            // Cek apakah payslip sudah ada
            $existingPayslip = self::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$existingPayslip) {
                // Hitung basic salary berdasarkan tipe gaji
                $basic_salary = $employee->salary;

                // Generate nomor payslip
                $payslip_number = strtoupper(substr($employee->name, 0, 3)) . '-' . $month . $year . '-' . rand(1000, 9999);

                // Buat payslip baru
                $payslip = new self();
                $payslip->employee_id = $employee->id;
                $payslip->payslip_number = $payslip_number;
                $payslip->month = $month;
                $payslip->year = $year;
                $payslip->salary_type = $employee->salary_type;
                $payslip->basic_salary = $basic_salary;
                $payslip->created_by = Auth::user()->type != 'super admin' ? $created_by : $employee->created_by;

                // Hitung Allowances (tunjangan)
                $totalAllowance = 0;

                // Allowance permanen
                $permanentAllowances = Allowance::where('employee_id', $employee->id)
                    ->where('type', 'permanent')
                    // ->where('created_by', $created_by)
                    ->when(Auth::user()->type != 'super admin', function ($q) use ($created_by) {
                        $q->where('created_by', $created_by);
                    })
                    ->get();

                // Allowance bulanan
                $monthlyAllowances = Allowance::where('employee_id', $employee->id)
                    ->where('type', 'monthly')
                    ->where('month', sprintf('%02d', $month)) // Format menjadi 2 digit (03 bukan 3)
                    ->where('year', (int)$year)
                    // ->where('created_by', $created_by)
                    ->when(Auth::user()->type != 'super admin', function ($q) use ($created_by) {
                        $q->where('created_by', $created_by);
                    })
                    ->get();

                $allowances = $permanentAllowances->merge($monthlyAllowances);
                $allowance_json = json_encode($allowances);

                // Menambahkan allowances ke payslip dan menghitung total
                foreach ($allowances as $allowance) {
                    $totalAllowance += $allowance->amount;
                }

                // Hitung Deductions (potongan)
                $totalDeduction = 0;

                // Deduction permanen
                $permanentDeductions = Deduction::where('employee_id', $employee->id)
                    ->where('type', 'permanent')
                    // ->where('created_by', $created_by)
                    ->when(Auth::user()->type != 'super admin', function ($q) use ($created_by) {
                        $q->where('created_by', $created_by);
                    })
                    ->get();

                // Deduction bulanan
                $monthlyDeductions = Deduction::where('employee_id', $employee->id)
                    ->where('type', 'monthly')
                    ->where('month', sprintf('%02d', $month)) // Format menjadi 2 digit (03 bukan 3)
                    ->where('year', (int)$year)
                    // ->where('created_by', $created_by)
                    ->when(Auth::user()->type != 'super admin', function ($q) use ($created_by) {
                        $q->where('created_by', $created_by);
                    })
                    ->get();

                $deductions = $permanentDeductions->merge($monthlyDeductions);
                $deduction_json = json_encode($deductions);

                // Menambahkan deductions ke payslip dan menghitung total
                foreach ($deductions as $deduction) {
                    $totalDeduction += $deduction->amount;
                }

                // Hitung Overtime
                $overtimeTotal = 0;
                $approvedOvertimes = Overtime::where('employee_id', $employee->id)
                    ->whereMonth('overtime_date', $month)
                    ->whereYear('overtime_date', $year)
                    ->where('status', 'approved')
                    ->get();
                $overtime_json = json_encode($approvedOvertimes);

                foreach ($approvedOvertimes as $overtime) {
                    $overtimeAmount = 0;

                    $total_work      = $overtime->number_of_days * $overtime->hours;
                    $overtimeAmount          = $total_work * $overtime->rate;

                    $overtimeTotal += $overtimeAmount;
                }
                // dd($basic_salary, $totalAllowance, $overtimeTotal, $totalDeduction);
                // Update total allowance, deduction, dan net salary
                $payslip->total_allowance = $totalAllowance;
                $payslip->total_deduction = $totalDeduction;
                $payslip->total_overtime = $overtimeTotal;
                $payslip->allowance = $allowance_json;
                $payslip->deduction = $deduction_json;
                $payslip->overtime = $overtime_json;
                $payslip->net_salary = $basic_salary + $totalAllowance + $overtimeTotal - $totalDeduction;
                $payslip->payment_status = 'unpaid';
                $payslip->save();

                $payslips[] = $payslip;
            }
        }

        return $payslips;
    }

    // Mendapatkan payslip untuk satu karyawan
    public function getEmployeePayslips($employee_id, $year = null)
    {
        $query = $this->where('employee_id', $employee_id)
            ->with(['employee', 'allowances', 'deductions', 'overtimes']);

        if ($year) {
            $query->where('year', $year);
        }

        return $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    // Update status pembayaran
    public function updatePaymentStatus($payslip_id, $status, $payment_method = null, $payment_date = null)
    {
        $payslip = $this->findOrFail($payslip_id);
        $payslip->payment_status = $status;

        if ($payment_method) {
            $payslip->payment_method = $payment_method;
        }

        if ($payment_date) {
            $payslip->payment_date = $payment_date;
        } else if ($status == 'paid') {
            $payslip->payment_date = Carbon::now()->format('Y-m-d');
        }

        return $payslip->save();
    }
}
