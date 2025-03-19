<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\RewardType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, create some reward types if they don't exist
        $rewardTypes = [
            [
                'name' => 'Employee of the Month',
                'description' => 'Awarded to employees who demonstrate exceptional performance and dedication.'
            ],
            [
                'name' => 'Achievement Award',
                'description' => 'For completing significant milestones or projects.'
            ],
            [
                'name' => 'Innovation Award',
                'description' => 'For employees who introduce new ideas or improvements.'
            ],
            [
                'name' => 'Leadership Award',
                'description' => 'For demonstrating exceptional leadership qualities.'
            ],
            [
                'name' => 'Teamwork Award',
                'description' => 'For outstanding collaboration and team spirit.'
            ]
        ];

        foreach ($rewardTypes as $type) {
            RewardType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description']]
            );
        }

        // Get all employee IDs
        $employeeIds = Employee::pluck('id')->toArray();

        // Get all reward type IDs
        $rewardTypeIds = RewardType::pluck('id')->toArray();

        // If there are no employees or reward types, stop execution
        if (empty($employeeIds) || empty($rewardTypeIds)) {
            $this->command->info('No employees or reward types found. Please seed employees first.');
            return;
        }

        // Sample gift items
        $gifts = [
            'Gift Card - Rp 500.000',
            'Smart Watch',
            'Certificate and Trophy',
            'Weekend Getaway Package',
            'Dining Voucher - Rp 1.000.000',
            'Additional Day Off',
            'Team Lunch',
            null  // Some rewards might not have gifts
        ];

        // Create 30 sample rewards
        $rewards = [];
        $now = Carbon::now();

        for ($i = 0; $i < 30; $i++) {
            $employeeId = $employeeIds[array_rand($employeeIds)];
            $rewardTypeId = $rewardTypeIds[array_rand($rewardTypeIds)];
            $gift = $gifts[array_rand($gifts)];

            // Generate a random date within the last year
            $date = Carbon::now()->subDays(rand(1, 365));

            $rewards[] = [
                'employee_id' => $employeeId,
                'reward_type_id' => $rewardTypeId,
                'date' => $date,
                'gift' => $gift,
                'description' => $this->generateDescription($rewardTypeId),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert rewards in batches
        foreach (array_chunk($rewards, 10) as $chunk) {
            DB::table('rewards')->insert($chunk);
        }
    }

    /**
     * Generate a description based on reward type
     *
     * @param int $rewardTypeId
     * @return string
     */
    private function generateDescription($rewardTypeId)
    {
        $descriptions = [
            // Employee of the Month
            1 => [
                'For consistently exceeding targets and maintaining excellent customer satisfaction scores.',
                'Recognized for perfect attendance and exceptional service quality throughout the month.',
                'For going above and beyond to assist colleagues and improve team performance.',
            ],
            // Achievement Award
            2 => [
                'Successfully completed the project ahead of schedule and under budget.',
                'Achieved 150% of quarterly sales target.',
                'For completing certification and implementing new skills to improve department efficiency.',
            ],
            // Innovation Award
            3 => [
                'Developed a new process that reduced operating costs by 15%.',
                'Created an innovative solution to a long-standing customer issue.',
                'Designed and implemented a new reporting system that saved 10 hours per week.',
            ],
            // Leadership Award
            4 => [
                'Led the team through a challenging transition period with positive results.',
                'Mentored three junior employees who have now been promoted.',
                'Demonstrated exceptional crisis management during system outage.',
            ],
            // Teamwork Award
            5 => [
                'Consistently supported team members and fostered a collaborative environment.',
                'Volunteered to help other departments during peak periods.',
                'Key contributor to cross-functional project success.',
            ],
        ];

        // If reward type doesn't match, use a generic description
        if (!isset($descriptions[$rewardTypeId])) {
            return 'Recognized for outstanding contribution to the company.';
        }

        $typeDescriptions = $descriptions[$rewardTypeId];
        return $typeDescriptions[array_rand($typeDescriptions)];
    }
}
