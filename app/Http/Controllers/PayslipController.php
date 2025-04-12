<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\User;
use App\Models\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PayslipController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Pay Slip')) {
            $month = intval(date('m'));
            $user = User::find(Auth::user()->id);
            if (Auth::user()->type == 'super admin') {
                $payslips = Payslip::with(['company', 'employee', 'employee.branch', 'employee.department', 'employee.designation'])
                    ->where('month', $month)
                    ->get();
            } else {
                $payslips = Payslip::with(['employee', 'employee.branch', 'employee.department', 'employee.designation'])
                    ->where('month', $month)
                    ->where('created_by', $user->creatorId())
                    ->get();
            }

            // Tambahkan net_salary untuk setiap employee
            // foreach ($payslips as $payslip) {
            //     $payslip->employee->net_salary = $payslip->employee->calculate_net_salary();
            // }

            return response()->json([
                'status' => true,
                'message' => 'Payslip retrieved successfully',
                'data' => $payslips
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('Manage Pay Slip')) {
            $user = User::find(Auth::user()->id);
            $payslip = Payslip::with(['employee', 'employee.branch', 'employee.department'])
                // ->where('created_by', $user->id)
                ->when($user->type == 'company', function ($q) use ($user) {
                    $q->where('created_by', $user->creatorId());
                })
                ->findOrFail($id);

            // Decode JSON data stored in the payslip
            if ($payslip->allowance) {
                $payslip->allowance_data = json_decode($payslip->allowance);
            }

            if ($payslip->deduction) {
                $payslip->deduction_data = json_decode($payslip->deduction);
            }

            if ($payslip->overtime) {
                $payslip->overtime_data = json_decode($payslip->overtime);
            }

            return response()->json([
                'status' => true,
                'message' => 'Payslip retrieved successfully',
                'data' => $payslip
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find(Auth::user()->id);

        $payslip = Payslip::with(['employee', 'employee.branch', 'employee.department'])
            ->when(Auth::user()->type != 'super admin', function ($q) use ($user) {
                $q->where('created_by', $user->creatorId());
            })
            ->findOrFail($id);

        $payslip->payment_status = $request->payment_status;
        $payslip->save();

        return response()->json([
            'status' => true,
            'message' => 'Payslip payment status updated successfully',
            'data' => $payslip
        ], 200);
    }

    /**
     * Generate payslips for all employees for a specific month/year
     */
    public function generatePayslips(Request $request)
    {
        if (!Auth::user()->can('Create Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        // dd($request->all());
        $month = $request->month;
        $year = $request->year;
        $user = User::find(Auth::user()->id);

        try {
            // Check if payslips for the month already exist
            // $existingPayslips = Payslip::where('month', $month)
            //     ->where('year', $year)
            //     // ->where('created_by', $user->creatorId())
            //     ->when(Auth::user()->type != 'super admin', function ($q) use ($user) {
            //         $q->where('created_by', $user->creatorId());
            //     })
            //     ->count();

            // if ($existingPayslips > 0) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Payslips for this month and year already exist.',
            //     ], 409);
            // }

            // Generate payslips using the model method
            $payslips = Payslip::generatePayslip($month, $year, $user->creatorId());

            // Generate PDF for each payslip (optional)
            foreach ($payslips as $payslip) {
                $this->generatePayslipPdf($payslip->id);
            }

            return response()->json([
                'status' => true,
                'message' => 'Payslips generated successfully',
                'data' => [
                    'count' => count($payslips),
                    'month' => $month,
                    'year' => $year
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate payslips',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filtered payslips based on various criteria
     */
    public function getFilteredPayslips(Request $request)
    {
        if (!Auth::user()->can('Manage Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::find(Auth::user()->id);

        // Create a new payslip instance
        $payslipModel = new Payslip();

        // Use the model method to get filtered payslips
        $payslips = $payslipModel->getAllFilteredPayslips($request);

        return response()->json([
            'status' => true,
            'message' => 'Payslips retrieved successfully',
            'data' => $payslips
        ], 200);
    }


    /**
     * Generate a PDF for a specific payslip
     */
    private function generatePayslipPdf($payslipId)
    {
        $payslip = Payslip::with(['employee', 'employee.branch', 'employee.department', 'employee.designation'])
            ->findOrFail($payslipId);

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        if (!$payslip) {
            return false;
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
            'employee' => $payslip->employee,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'overtime' => $overtime,
            'monthName' => $monthName,
            'companyTz' => $companyTz
        ]);

        // Set filename
        $filename = 'payslip_' . $payslip->payslip_number . '.pdf';

        // Create directory if it doesn't exist
        $directory = 'payslips/' . $payslip->employee->id . '/' . $payslip->year;
        $path = Storage::disk('public')->path($directory);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // Save file to storage
        $fullPath = $directory . '/' . $filename;
        Storage::disk('public')->put($fullPath, $pdf->output());

        // Generate URL for the file
        $url = Storage::disk('public')->url($fullPath);

        // Update the payslip with the file URL
        $payslip->file_url = $url;
        $payslip->save();

        return true;
    }


    public function getPayslipPdf($id)
    {
        if (!Auth::user()->can('Manage Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        // Get the specific payslip
        $payslip = Payslip::with(['employee', 'employee.branch', 'employee.department'])
            ->where('id', $id)
            ->first();

        if (!$payslip) {
            return response()->json([
                'status' => false,
                'message' => 'Payslip not found or you do not have access.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payslip retrieved successfully',
            'data' => [
                'payslip_number' => $payslip->payslip_number,
                'file_url' => $payslip->file_url
            ]
        ], 200);
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Pay Slip')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        try {
            $payslip = Payslip::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payslip not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Soft delete the project (using SoftDeletes trait)
            $payslip->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payslip deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Payslip',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
