<?php

namespace Database\Seeders;

use App\Models\TerminationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TerminationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $terminationTypes = [
            [
                'name' => 'Voluntary Resignation',
                'created_by' => 2, // Assuming admin user ID is 2
            ],
            [
                'name' => 'Contract Completion',
                'created_by' => 2,
            ],
            [
                'name' => 'Performance Issues',
                'created_by' => 2,
            ],
            [
                'name' => 'Redundancy',
                'created_by' => 2,
            ],
            [
                'name' => 'Misconduct',
                'created_by' => 2,
            ],
            [
                'name' => 'Early Retirement',
                'created_by' => 2,
            ],
            [
                'name' => 'Abandonment of Position',
                'created_by' => 2,
            ],
            [
                'name' => 'Probation Failure',
                'created_by' => 2,
            ],
            [
                'name' => 'Mutual Agreement',
                'created_by' => 2,
            ],
            [
                'name' => 'Other',
                'created_by' => 2,
            ],
        ];

        // Check if records already exist before inserting
        if (TerminationType::count() === 0) {
            foreach ($terminationTypes as $terminationType) {
                TerminationType::create($terminationType);
            }
        }
    }
}
