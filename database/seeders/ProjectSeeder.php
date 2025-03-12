<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get a creator user (assuming admin user exists with ID 2)
        $creator = User::find(2);

        if (!$creator) {
            $this->command->error('Creator user not found. Make sure to run UserSeeder first.');
            return;
        }

        // Get some employees to assign as project members
        $employees = Employee::take(2)->get();
        $users = User::where('type', '!=', 'super admin')
            ->get();

        if ($users->isEmpty()) {
            $this->command->error('No employees found. Make sure to run EmployeeSeeder first.');
            return;
        }

        // Current date info for realistic project dates
        $now = Carbon::now();

        // Create active projects
        $this->createProjectsWithStatus('active', 5, $creator, $users, $now);

        // Create on_hold projects
        $this->createProjectsWithStatus('on_hold', 3, $creator, $users, $now);

        // Create completed projects
        $this->createProjectsWithStatus('completed', 4, $creator, $users, $now);

        $this->command->info('Projects seeded successfully.');
    }

    /**
     * Create projects with a specific status
     *
     * @param string $status
     * @param int $count
     * @param User $creator
     * @param \Illuminate\Database\Eloquent\Collection $employees
     * @param Carbon $now
     * @return void
     */
    private function createProjectsWithStatus($status, $count, $creator, $employees, $now)
    {
        for ($i = 1; $i <= $count; $i++) {
            // Create the project
            $project = Project::create([
                'name' => ucfirst($status) . ' Project ' . $i,
                'description' => 'This is a sample ' . $status . ' project created by the seeder.',
                'status' => $status,
                'start_date' => $now->copy()->subDays(rand(10, 60))->format('Y-m-d'),
                'end_date' => $this->getEndDate($status, $now),
                'created_by' => $creator->id,
                'created_at' => $now->copy()->subDays(rand(10, 60)),
                'updated_at' => $now->copy()->subDays(rand(0, 10)),
            ]);

            // Attach random members to the project
            $memberCount = rand(1, min(count($employees), 4));
            $memberIds = $employees->random($memberCount)->pluck('id')->toArray();

            foreach ($memberIds as $employeeId) {
                $project->members()->attach($employeeId, [
                    'assigned_by' => $creator->id  // Use the creator's ID as the assigner
                ]);
            }
        }
    }

    /**
     * Get end date based on project status
     *
     * @param string $status
     * @param Carbon $now
     * @return string
     */
    private function getEndDate($status, $now)
    {
        switch ($status) {
            case 'completed':
                // Completed projects should have end dates in the past
                return $now->copy()->subDays(rand(1, 20))->format('Y-m-d');
            case 'on_hold':
                // On hold projects usually have future end dates
                return $now->copy()->addDays(rand(30, 90))->format('Y-m-d');
            case 'active':
            default:
                // Active projects have future end dates
                return $now->copy()->addDays(rand(10, 60))->format('Y-m-d');
        }
    }
}
