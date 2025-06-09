<?php

namespace App\Imports;

use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\SalaryComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SalaryComponentImport implements ToCollection, WithHeadingRow
{
    private $importedData = [];
    private $errors = [];
    private $currentRow = 1; // Start from row 1 (excluding header)

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $this->currentRow = $index + 2; // +2 karena index dimulai dari 0 dan ada header

            try {

                if ($row['category'] == 'allowance') {
                    $allowance = Allowance::where('employee_id', $row['employee_id'])
                        ->where('id', $row['salary_component_id'])->first();
                    if (!empty($allowance)) {
                        $allowance->update([
                            'title'     => $row['salary_component_name'],
                            'amount'    => $row['amount'],
                            'type'      => $row['type'],
                        ]);
                        $allowance->refresh();
                        $this->importedData[] = $allowance->toArray();
                    } else {
                        $allowance = Allowance::create([
                            'employee_id'   => $row['employee_id'],
                            'title'     => $row['salary_component_name'],
                            'amount'    => $row['amount'],
                            'type'      => $row['type'],
                        ]);
                        $this->importedData[] = $allowance->toArray();
                    }
                }

                if ($row['category'] == 'deduction') {
                    $deduction = Deduction::where('employee_id', $row['employee_id'])
                        ->where('id', $row['salary_component_id'])->first();
                    if (!empty($deduction)) {
                        $deduction->update([
                            'title'     => $row['salary_component_name'],
                            'amount'    => $row['amount'],
                            'type'      => $row['type'],
                        ]);
                        $deduction->refresh();
                        $this->importedData[] = $deduction->toArray();
                    } else {
                        $deduction = Deduction::create([
                            'employee_id'   => $row['employee_id'],
                            'title'     => $row['salary_component_name'],
                            'amount'    => $row['amount'],
                            'type'      => $row['type'],
                        ]);
                        $this->importedData[] = $deduction->toArray();
                    }
                }


                // if ($existingComponent) {
                //     // Update existing record
                //     $existingComponent->update($validatedData);
                //     $this->importedData[] = $existingComponent->toArray();
                // } else {
                //     // Create new record
                //     $salaryComponent = SalaryComponent::create($validatedData);
                //     $this->importedData[] = $salaryComponent->toArray();
                // }
                // }
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'message' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
            }
        }
    }

    /**
     * Validasi data per row
     */
    private function validateRow(array $row)
    {
        // dd($row);


        // Mapping kolom Excel ke field database
        $mappedData = [
            'name' => $row['nama_komponen'] ?? $row['name'] ?? null,
            'code' => $row['kode_komponen'] ?? $row['code'] ?? null,
            'type' => $row['tipe'] ?? $row['type'] ?? null,
            'amount' => $row['jumlah'] ?? $row['amount'] ?? 0,
            'is_active' => $row['status'] ?? $row['is_active'] ?? true,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
        ];

        // Rules validasi
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|in:allowance,deduction,bonus', // Sesuaikan dengan enum Anda
            'amount' => 'numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ];

        // Custom messages
        $messages = [
            'name.required' => 'Nama komponen wajib diisi',
            'code.required' => 'Kode komponen wajib diisi',
            'type.required' => 'Tipe komponen wajib diisi',
            'type.in' => 'Tipe komponen harus salah satu dari: allowance, deduction, bonus',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah tidak boleh negatif',
        ];

        $validator = Validator::make($mappedData, $rules, $messages);

        if ($validator->fails()) {
            $this->errors[] = [
                'row' => $this->currentRow,
                'errors' => $validator->errors()->toArray(),
                'data' => $mappedData
            ];
            return false;
        }

        // Convert status text to boolean if needed
        if (isset($mappedData['is_active']) && is_string($mappedData['is_active'])) {
            $mappedData['is_active'] = in_array(strtolower($mappedData['is_active']), ['aktif', 'active', '1', 'true', 'ya', 'yes']);
        }

        return $mappedData;
    }

    /**
     * Get imported data
     */
    public function getImportedData()
    {
        return $this->importedData;
    }

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
