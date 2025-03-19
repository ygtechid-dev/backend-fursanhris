<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Employee;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing promotions
        DB::table('promotions')->truncate();

        // Get all employees and designations
        $employees = Employee::all();
        $designations = Designation::all();

        // Skip if no employees or designations
        if ($employees->isEmpty() || $designations->isEmpty()) {
            echo "Skipping promotion seeding: No employees or designations found.\n";
            return;
        }

        // Sample promotion titles and descriptions
        $promotionTitles = [
            'Performance Recognition',
            'Career Advancement',
            'Department Transfer',
            'Annual Promotion',
            'Special Achievement Recognition',
            'Management Role Upgrade',
            'Team Lead Appointment',
            'Quarterly Assessment Promotion',
            'Project Success Recognition',
            'Skill Development Promotion'
        ];

        $descriptions = [
            'Promoted based on excellent performance and consistent results over the past year.',
            'Career progression advancement after completing required skill certification.',
            'Transferred to a new department with higher responsibilities and compensation.',
            'Regular annual promotion as per company policy.',
            'Recognized for outstanding contributions to critical company projects.',
            'Elevated to management position after leadership training completion.',
            'Appointed as team lead after demonstrating exceptional leadership qualities.',
            'Promoted following quarterly performance assessment results.',
            'Recognition for successful completion of strategic company project.',
            'Promoted after acquiring new skills relevant to higher position requirements.'
        ];

        // Create 20 sample promotions
        $promotionData = [];
        $count = min(20, $employees->count());
        $creatorIds = $employees->pluck('created_by')->unique()->filter()->toArray();
        $defaultCreatorId = !empty($creatorIds) ? $creatorIds[0] : 1;

        for ($i = 0; $i < $count; $i++) {
            $employee = $employees->random();
            $newDesignation = $designations->random();
            $titleIndex = array_rand($promotionTitles);

            // Generate a random date within the last 2 years
            $date = Carbon::now()->subDays(rand(1, 730))->format('Y-m-d');

            $promotionData[] = [
                'employee_id' => $employee->id,
                'designation_id' => $newDesignation->id,
                'promotion_title' => $promotionTitles[$titleIndex],
                'promotion_date' => $date,
                'description' => $descriptions[$titleIndex],
                'created_by' => $employee->created_by ?? $defaultCreatorId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Insert data in chunks to avoid memory issues
        foreach (array_chunk($promotionData, 5) as $chunk) {
            Promotion::insert($chunk);
        }

        echo "Added {$count} sample promotions.\n";
    }
}
