<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use App\Models\DeductionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayrollTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin ID (diasumsikan admin dengan ID 1)
        $admin_id = 2;

        // Jenis Tunjangan Umum (Allowances)
        $allowanceTypes = [
            [
                'name' => 'Tunjangan Makan',
                'is_taxable' => false,
                'description' => 'Tunjangan untuk biaya makan karyawan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Tunjangan Transport',
                'is_taxable' => false,
                'description' => 'Tunjangan untuk biaya transportasi karyawan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Tunjangan Jabatan',
                'is_taxable' => true,
                'description' => 'Tunjangan berdasarkan jabatan karyawan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Tunjangan Keluarga',
                'is_taxable' => true,
                'description' => 'Tunjangan untuk karyawan yang sudah berkeluarga',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Bonus',
                'is_taxable' => true,
                'description' => 'Bonus kinerja atau bonus tahunan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Tunjangan Lembur',
                'is_taxable' => true,
                'description' => 'Tunjangan untuk jam kerja tambahan',
                'created_by' => $admin_id
            ],
        ];

        // Jenis Potongan Umum (Deductions)
        $deductionTypes = [
            [
                'name' => 'BPJS Kesehatan',
                'is_mandatory' => true,
                'description' => 'Potongan untuk BPJS Kesehatan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'BPJS Ketenagakerjaan',
                'is_mandatory' => true,
                'description' => 'Potongan untuk BPJS Ketenagakerjaan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'PPh 21',
                'is_mandatory' => true,
                'description' => 'Pajak Penghasilan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Pinjaman',
                'is_mandatory' => false,
                'description' => 'Potongan untuk pembayaran pinjaman karyawan',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Keterlambatan',
                'is_mandatory' => false,
                'description' => 'Potongan untuk keterlambatan kehadiran',
                'created_by' => $admin_id
            ],
            [
                'name' => 'Absensi',
                'is_mandatory' => false,
                'description' => 'Potongan untuk ketidakhadiran tanpa izin',
                'created_by' => $admin_id
            ],
        ];

        // Insert data
        foreach ($allowanceTypes as $type) {
            AllowanceType::create($type);
        }

        foreach ($deductionTypes as $type) {
            DeductionType::create($type);
        }
    }
}
