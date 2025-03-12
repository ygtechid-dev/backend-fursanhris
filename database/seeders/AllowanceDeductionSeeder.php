<?php

namespace Database\Seeders;

use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AllowanceDeductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ambil ID creator
        $created_by = 2;

        // Ambil semua employee
        $employees = Employee::where('created_by', $created_by)->get();

        if ($employees->isEmpty()) {
            $this->command->info('Tidak ada employee ditemukan. Silakan tambahkan employee terlebih dahulu.');
            return;
        }

        $month = date('m');
        $year = date('Y');
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        foreach ($employees as $employee) {
            // Tambahkan allowance permanen untuk setiap karyawan
            Allowance::create([
                'employee_id' => $employee->id,
                'title' => 'Tunjangan Transportasi',
                'type' => 'permanent',
                'amount' => 500000,
                'created_by' => $created_by
            ]);

            Allowance::create([
                'employee_id' => $employee->id,
                'title' => 'Tunjangan Makan',
                'type' => 'permanent',
                'amount' => 800000,
                'created_by' => $created_by
            ]);

            // Tambahkan allowance bulanan untuk bulan ini
            Allowance::create([
                'employee_id' => $employee->id,
                'title' => 'Bonus Performa',
                'type' => 'monthly',
                'month' => $month,
                'year' => $year,
                'amount' => rand(200000, 1000000),
                'created_by' => $created_by
            ]);

            // Tambahkan allowance bulanan untuk bulan sebelumnya
            $lastMonth = Carbon::now()->subMonth();
            Allowance::create([
                'employee_id' => $employee->id,
                'title' => 'Bonus Performa',
                'type' => 'monthly',
                'month' => $lastMonth->format('m'),
                'year' => $lastMonth->format('Y'),
                'amount' => rand(200000, 1000000),
                'created_by' => $created_by
            ]);

            // Tambahkan deduction permanen untuk setiap karyawan
            Deduction::create([
                'employee_id' => $employee->id,
                'title' => 'BPJS Kesehatan',
                'type' => 'permanent',
                'amount' => 150000,
                'created_by' => $created_by
            ]);

            Deduction::create([
                'employee_id' => $employee->id,
                'title' => 'BPJS Ketenagakerjaan',
                'type' => 'permanent',
                'amount' => 100000,
                'created_by' => $created_by
            ]);

            // Tambahkan deduction bulanan untuk bulan ini
            Deduction::create([
                'employee_id' => $employee->id,
                'title' => 'Potongan Keterlambatan',
                'type' => 'monthly',
                'month' => $month,
                'year' => $year,
                'amount' => rand(0, 200000),
                'created_by' => $created_by
            ]);

            // Tambahkan deduction bulanan untuk bulan sebelumnya
            Deduction::create([
                'employee_id' => $employee->id,
                'title' => 'Potongan Keterlambatan',
                'type' => 'monthly',
                'month' => $lastMonth->format('m'),
                'year' => $lastMonth->format('Y'),
                'amount' => rand(0, 200000),
                'created_by' => $created_by
            ]);


            for ($i = 0; $i < 2; $i++) {
                $day = 10 + $i; // Tanggal 10 dan 11
                $date = $currentMonth->copy()->setDay($day);

                Overtime::create([
                    'employee_id' => $employee->id,
                    'overtime_date' => $date->format('Y-m-d'),
                    'hours' => 2,
                    'number_of_days' => 1,
                    'rate' => 50000,
                    'start_time' => '18:00:00',
                    'end_time' => '20:00:00',
                    'remark' => 'Lembur proyek X',
                    'status' => 'approved',
                    'created_by' => $created_by
                ]);
            }
        }

        $this->command->info('Seeder Allowance dan Deduction berhasil dijalankan!');
    }
}
