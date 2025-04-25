<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Pastikan ada data employees dan users
        $employees = Employee::all();
        $company = User::find(2); // Asumsikan user pertama adalah perusahaan/admin

        if ($employees->count() > 0 && $company) {
            // Data asset contoh
            $assetData = [
                [
                    'name' => 'Laptop Kerja',
                    'brand' => 'Dell',
                    'warranty_status' => 'On',
                    'buying_date' => '2023-01-15',
                    'image' => 'laptop-dell.jpg',
                ],
                [
                    'name' => 'Komputer Desktop',
                    'brand' => 'HP',
                    'warranty_status' => 'On',
                    'buying_date' => '2023-02-20',
                    'image' => 'desktop-hp.jpg',
                ],
                [
                    'name' => 'Smartphone',
                    'brand' => 'Samsung',
                    'warranty_status' => 'On',
                    'buying_date' => '2023-03-10',
                    'image' => 'smartphone-samsung.jpg',
                ],
                [
                    'name' => 'Monitor',
                    'brand' => 'LG',
                    'warranty_status' => 'Off',
                    'buying_date' => '2022-05-05',
                    'image' => 'monitor-lg.jpg',
                ],
                [
                    'name' => 'Printer',
                    'brand' => 'Epson',
                    'warranty_status' => 'On',
                    'buying_date' => '2023-06-12',
                    'image' => 'printer-epson.jpg',
                ]
            ];

            // Pastikan hanya satu asset untuk satu employee
            foreach ($employees as $index => $employee) {
                // Cek apakah employee sudah memiliki asset
                $existingAsset = Asset::where('employee_id', $employee->id)->first();

                if (!$existingAsset && isset($assetData[$index])) {
                    Asset::create([
                        'employee_id' => $employee->id,
                        'name' => $assetData[$index]['name'],
                        'brand' => $assetData[$index]['brand'],
                        'warranty_status' => $assetData[$index]['warranty_status'],
                        'buying_date' => $assetData[$index]['buying_date'],
                        'image' => $assetData[$index]['image'],
                        'created_by' => $company->id,
                    ]);
                }
            }
        }
    }
}
