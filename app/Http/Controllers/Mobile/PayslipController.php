<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\User;
use App\Models\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PayslipController extends Controller
{
    /**
     * Display a listing of all payslips for the authenticated employee
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Check if user has permission to view their payslips
        if (!Auth::user()->can('Manage Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();

        if (!$user->employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee profile not found.',
            ], 404);
        }

        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get filter parameters
        $year = $request->input('year', date('Y'));

        // Get payslips for the authenticated employee
        $payslips = Payslip::where('employee_id', $employee->id)
            ->when($year, function ($query, $year) {
                return $query->where('year', $year);
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($payslip) use ($companyTz) {
                return $this->formatPayslipResponse($payslip, $companyTz);
            });

        // Get years with payslips for filtering
        $availableYears = Payslip::where('employee_id', $employee->id)
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Count payslips by payment status
        $statusCounts = [
            'paid' => Payslip::where('employee_id', $employee->id)
                ->where('payment_status', 'paid')
                ->count(),
            'unpaid' => Payslip::where('employee_id', $employee->id)
                ->where('payment_status', 'unpaid')
                ->count(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Payslips retrieved successfully',
            'data' => [
                'payslips' => $payslips,
                'available_years' => $availableYears,
                'status_counts' => $statusCounts,
            ]
        ], 200);
    }

    /**
     * Display the details of a specific payslip
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Check if user has permission to Manage Pay Slip
        if (!Auth::user()->can('Manage Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();

        if (!$user->employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee profile not found.',
            ], 404);
        }

        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get the specific payslip
        $payslip = Payslip::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$payslip) {
            return response()->json([
                'status' => false,
                'message' => 'Payslip not found or you do not have access.',
            ], 404);
        }

        // Format detailed payslip response
        $response = $this->formatDetailedPayslipResponse($payslip, $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Payslip details retrieved successfully',
            'data' => $response
        ], 200);
    }

    /**
     * Export payslip as PDF and save to storage
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function exportPdf($id)
    {
        // Check if user has permission to export payslip
        if (!Auth::user()->can('Manage Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        if (!$user->employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee profile not found.',
            ], 404);
        }

        $employee = $user->employee;

        // Get the specific payslip
        $payslip = Payslip::with(['employee', 'employee.branch', 'employee.department'])
            ->where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$payslip) {
            return response()->json([
                'status' => false,
                'message' => 'Payslip not found or you do not have access.',
            ], 404);
        }

        // Parse JSON data for allowances, deductions, and overtime
        $allowances = json_decode($payslip->allowance, true) ?? [];
        $deductions = json_decode($payslip->deduction, true) ?? [];
        $overtime = json_decode($payslip->overtime, true) ?? [];

        // Get the month name
        $monthName = date('F', mktime(0, 0, 0, $payslip->month, 10));

        // Generate PDF
        $pdf = PDF::loadView('pdf.payslips-pdf', [
            'payslip' => $payslip,
            'employee' => $employee,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'overtime' => $overtime,
            'monthName' => $monthName,
            'companyTz' => $companyTz
        ]);

        // Set filename
        $filename = 'payslip_' . $payslip->payslip_number . '.pdf';

        // Create directory if it doesn't exist
        $directory = 'payslips/' . $employee->id . '/' . $payslip->year;
        $path = Storage::disk('public')->path($directory);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // Save file to storage
        $fullPath = $directory . '/' . $filename;
        Storage::disk('public')->put($fullPath, $pdf->output());

        // Generate URL for the file
        $url = Storage::disk('public')->url($fullPath);

        $payslip->update([
            'file_url'  => $url
        ]);

        // Return response with file URL
        return response()->json([
            'status' => true,
            'message' => 'Payslip generated successfully',
            'data' => [
                'payslip_number' => $payslip->payslip_number,
                'file_name' => $filename,
                'file_url' => $url
            ]
        ], 200);
    }

    /**
     * Format basic payslip data for response
     *
     * @param Payslip $payslip
     * @param string $timezone
     * @return array
     */
    private function formatPayslipResponse($payslip, $timezone)
    {
        // Get month name
        $monthName = date('F', mktime(0, 0, 0, $payslip->month, 10));

        return [
            'id' => $payslip->id,
            'payslip_number' => $payslip->payslip_number,
            'month' => $payslip->month,
            'month_name' => $monthName,
            'year' => $payslip->year,
            'period' => $monthName . ' ' . $payslip->year,
            'net_salary' => $payslip->net_salary,
            'total_work_hours' => $payslip->total_work_hours,
            'payment_status' => $payslip->payment_status,
            'payment_date' => $payslip->payment_date,
            'created_at' => Carbon::parse($payslip->created_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format detailed payslip data for response
     *
     * @param Payslip $payslip
     * @param string $timezone
     * @return array
     */
    private function formatDetailedPayslipResponse($payslip, $timezone)
    {
        // Get month name
        $monthName = date('F', mktime(0, 0, 0, $payslip->month, 10));

        // Parse JSON data
        $allowances = json_decode($payslip->allowance, true) ?? [];
        $deductions = json_decode($payslip->deduction, true) ?? [];
        $overtime = json_decode($payslip->overtime, true) ?? [];

        // Format allowances for response
        $formattedAllowances = [];
        foreach ($allowances as $allowance) {
            $formattedAllowances[] = [
                'title' => $allowance['title'] ?? 'Untitled Allowance',
                'type' => $allowance['type'] ?? 'unknown',
                'amount' => $allowance['amount'] ?? 0,
            ];
        }

        // Format deductions for response
        $formattedDeductions = [];
        foreach ($deductions as $deduction) {
            $formattedDeductions[] = [
                'title' => $deduction['title'] ?? 'Untitled Deduction',
                'type' => $deduction['type'] ?? 'unknown',
                'amount' => $deduction['amount'] ?? 0,
            ];
        }

        // Format overtime for response
        $formattedOvertime = [];
        foreach ($overtime as $item) {
            $formattedOvertime[] = [
                'date' => $item['overtime_date'] ?? 'Unknown Date',
                'hours' => $item['hours'] ?? 0,
                'rate' => $item['rate'] ?? 0,
                'days' => $item['number_of_days'] ?? 1,
            ];
        }

        return [
            'id' => $payslip->id,
            'payslip_number' => $payslip->payslip_number,
            'month' => $payslip->month,
            'month_name' => $monthName,
            'year' => $payslip->year,
            'period' => $monthName . ' ' . $payslip->year,
            'salary_type' => $payslip->salary_type,
            'basic_salary' => $payslip->basic_salary,
            'total_allowance' => $payslip->total_allowance,
            'total_deduction' => $payslip->total_deduction,
            'total_overtime' => $payslip->total_overtime,
            'net_salary' => $payslip->net_salary,
            'payment_status' => $payslip->payment_status,
            'payment_method' => $payslip->payment_method,
            'payment_date' => $payslip->payment_date,
            'note' => $payslip->note,
            'file_url' => $payslip->file_url,
            'allowances' => $formattedAllowances,
            'deductions' => $formattedDeductions,
            'overtime' => $formattedOvertime,
            'employee' => [
                'id' => $payslip->employee->id,
                'name' => $payslip->employee->name,
                'position' => $payslip->employee->position,
                'department' => $payslip->employee->department ? $payslip->employee->department->name : null,
                'branch' => $payslip->employee->branch ? $payslip->employee->branch->name : null,
            ],
            'created_at' => Carbon::parse($payslip->created_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($payslip->updated_at)->setTimezone($timezone)->format('Y-m-d H:i:s'),
        ];
    }
}
