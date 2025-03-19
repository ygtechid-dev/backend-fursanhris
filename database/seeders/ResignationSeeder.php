<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Resignation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find a creator user (usually an admin)
        $creator = User::find(2);

        if (!$creator) {
            // Fallback to first user if no company type exists
            $creator = User::first();
        }

        if (!$creator) {
            $this->command->error('No users found to set as creator for resignations!');
            return;
        }

        // Get all employees to create resignations for
        $employees = Employee::where('created_by', $creator->id)->get();

        if ($employees->isEmpty()) {
            $this->command->error('No employees found to create resignations!');
            return;
        }

        // Create sample resignation data
        $resignationData = [
            [
                'notice_date' => Carbon::now()->subDays(60),
                'resignation_date' => Carbon::now()->subDays(30),
                'description' => 'Moving to another company for better growth opportunities.'
            ],
            [
                'notice_date' => Carbon::now()->subDays(45),
                'resignation_date' => Carbon::now()->subDays(15),
                'description' => 'Relocating to another city due to family reasons.'
            ],
            [
                'notice_date' => Carbon::now()->subDays(30),
                'resignation_date' => Carbon::now(),
                'description' => 'Taking a break to pursue higher education.'
            ],
            [
                'notice_date' => Carbon::now()->subDays(14),
                'resignation_date' => Carbon::now()->addDays(16),
                'description' => 'Shifting to a different industry for career advancement.'
            ],
            [
                'notice_date' => Carbon::now()->subDays(7),
                'resignation_date' => Carbon::now()->addDays(23),
                'description' => 'Starting my own business venture.'
            ]
        ];

        // Create resignations for some employees
        $employeeCount = min(count($employees), 5);

        for ($i = 0; $i < $employeeCount; $i++) {
            Resignation::create([
                'employee_id' => $employees[$i]->id,
                'notice_date' => $resignationData[$i]['notice_date'],
                'resignation_date' => $resignationData[$i]['resignation_date'],
                'description' => $resignationData[$i]['description'],
                'created_by' => $creator->id,
            ]);

            $this->command->info("Created resignation for employee: {$employees[$i]->name}");
        }

        $this->command->info("Total {$employeeCount} resignations created successfully!");
    }
}
