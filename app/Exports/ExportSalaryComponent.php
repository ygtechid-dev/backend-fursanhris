<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportSalaryComponent implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        $employees = Employee::get();
        return view('exports.salary-component', [
            'employees' => $employees
        ]);
    }
}
