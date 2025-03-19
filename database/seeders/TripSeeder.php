<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all employee IDs to reference in trips
        $employeeIds = Employee::pluck('id')->toArray();

        // If no employees exist, create some dummy data
        if (empty($employeeIds)) {
            // This is just a fallback, ideally you should run EmployeeSeeder first
            $employeeIds = [1, 2, 3, 4, 5];
        }

        $adminId = 2; // Assuming admin user has ID 1 for created_by field

        $purposes = [
            'Business Meeting',
            'Conference',
            'Training',
            'Client Visit',
            'Branch Inspection'
        ];

        $places = [
            'Jakarta',
            'Surabaya',
            'Bandung',
            'Yogyakarta',
            'Bali',
            'Medan',
            'Makassar'
        ];

        // Create 20 sample trips
        for ($i = 0; $i < 20; $i++) {
            $startDate = Carbon::now()->subDays(rand(1, 60));
            $endDate = (clone $startDate)->addDays(rand(1, 7));

            Trip::create([
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'purpose_of_visit' => $purposes[array_rand($purposes)],
                'place_of_visit' => $places[array_rand($places)],
                'description' => 'Trip details and agenda for ' . $places[array_rand($places)] . ' visit.',
                'created_by' => $adminId,
            ]);
        }
    }
}
