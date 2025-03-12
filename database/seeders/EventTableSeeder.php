<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get user IDs with type other than "super admin"
        $userIds = User::where('type', '!=', 'super admin')
            ->pluck('id')
            ->toArray();

        // Admin user ID (assuming admin has ID 1)
        $adminId = 1;

        // Sample event data
        $events = [
            [
                'title' => 'Team Meeting',
                'description' => 'Weekly team sync-up meeting',
                'color' => '#4287f5', // Blue
                'start_date' => Carbon::now()->addDays(1)->setHour(10)->setMinute(0),
                'end_date' => Carbon::now()->addDays(1)->setHour(11)->setMinute(30),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Project Deadline',
                'description' => 'Final submission for Q1 project',
                'color' => '#f54242', // Red
                'start_date' => Carbon::now()->addDays(5)->setHour(17)->setMinute(0),
                'end_date' => Carbon::now()->addDays(5)->setHour(17)->setMinute(0),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Company Holiday',
                'description' => 'Annual company holiday',
                'color' => '#42f545', // Green
                'start_date' => Carbon::now()->addMonth()->startOfDay(),
                'end_date' => Carbon::now()->addMonth()->endOfDay(),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Training Session',
                'description' => 'New software training',
                'color' => '#f5a442', // Orange
                'start_date' => Carbon::now()->addDays(3)->setHour(14)->setMinute(0),
                'end_date' => Carbon::now()->addDays(3)->setHour(16)->setMinute(0),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Client Meeting',
                'description' => 'Quarterly review with client',
                'color' => '#9042f5', // Purple
                'start_date' => Carbon::now()->addDays(7)->setHour(11)->setMinute(0),
                'end_date' => Carbon::now()->addDays(7)->setHour(12)->setMinute(30),
                'created_by' => $adminId,
            ],
        ];

        // Insert events and assign users
        foreach ($events as $eventData) {
            // Create the event
            $event = Event::create($eventData);

            // Randomly select 1-3 users to assign to this event
            $assigneeCount = rand(1, min(2, count($userIds)));
            $assignees = array_rand(array_flip($userIds), $assigneeCount);

            // Make sure $assignees is always an array even if only one user is selected
            if (!is_array($assignees)) {
                $assignees = [$assignees];
            }

            // Create pivot table entries
            foreach ($assignees as $userId) {
                DB::table('event_employees')->insert([
                    'event_id' => $event->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
