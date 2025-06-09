<?php

namespace App\Http\Controllers;

use App\Exports\ExportSalaryComponent;
use App\Imports\SalaryComponentImport;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeSalaryController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Set Salary')) {

            if (Auth::user()->type == 'super admin') {
                $employees = Employee::with('company')->get();
            } else {
                $employees = Employee::with('company')->where(
                    [
                        'created_by' => Auth::user()->creatorId(),
                    ]
                )->get();
            }

            // Tambahkan net_salary untuk setiap employee
            foreach ($employees as $employee) {
                $employee->net_salary = $employee->calculate_net_salary();
            }

            return response()->json([
                'status' => true,
                'message' => 'Employee Salary retrieved successfully',
                'data' => $employees
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }
    }

    public function exportSalaryComponent()
    {
        try {
            // Generate filename dengan timestamp untuk menghindari konflik
            $filename = 'salary-component-' . date('Y-m-d-H-i-s') . '.xlsx';

            // Path untuk menyimpan file (di storage/app/public/exports)
            $path = 'exports/' . $filename;

            // Store file ke storage
            Excel::store(new ExportSalaryComponent, $path, 'public');

            // Generate download URL
            $downloadUrl = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dibuat',
                'download_url' => $downloadUrl,
                'filename' => $filename
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importSalaryComponent(Request $request)
    {
        try {
            // Validasi request
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:2048'
            ]);

            // Cek apakah file ada
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 400);
            }

            $file = $request->file('file');

            // Validasi file
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak valid'
                ], 400);
            }

            // Import data dari Excel
            $import = new SalaryComponentImport();
            Excel::import($import, $file);

            // Ambil data yang berhasil diimport (jika ada)
            $importedData = $import->getImportedData() ?? [];
            $errors = $import->getErrors() ?? [];

            // Response berdasarkan hasil import
            if (empty($errors)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil di import',
                    'imported_count' => count($importedData),
                    'data' => $importedData
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Import selesai dengan beberapa error',
                    'imported_count' => count($importedData),
                    'errors' => $errors,
                    'data' => $importedData
                ], 422);
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle validation errors dari Excel
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimport file: ' . $e->getMessage()
            ], 500);
        }
    }
}
