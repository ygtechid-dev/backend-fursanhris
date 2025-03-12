<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get a creator user
        $creator = User::find(2);

        if (!$creator) {
            $this->command->error('Creator user not found. Make sure to run UserSeeder first.');
            return;
        }

        // Get employees to assign as task assignees
        // $employees = Employee::take(5)->get();
        $users = User::where('type', '!=', 'super admin')
            ->get();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Make sure to run UserSeeder first.');
            return;
        }

        // Get projects to assign tasks to
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->error('No projects found. Make sure to run ProjectSeeder first.');
            return;
        }

        $now = Carbon::now();

        // Create tasks for each project
        foreach ($projects as $project) {
            $this->createTasksForProject($project, $creator, $users, $now);
        }

        $this->command->info('Tasks seeded successfully.');
    }

    /**
     * Create tasks for a project
     * 
     * @param Project $project
     * @param User $creator
     * @param \Illuminate\Database\Eloquent\Collection $employees
     * @param Carbon $now
     * @return void
     */
    private function createTasksForProject($project, $creator, $users, $now)
    {
        $taskCount = rand(3, 10);
        $statuses = ['todo', 'in_progress', 'done'];
        $priorities = ['low', 'medium', 'high'];

        // Distribute task statuses based on project status
        $statusDistribution = $this->getStatusDistributionByProjectStatus($project->status);

        for ($i = 1; $i <= $taskCount; $i++) {
            // Determine task status based on distribution
            $randomValue = rand(1, 100);
            $status = $this->getStatusFromDistribution($randomValue, $statusDistribution);

            // Set due date based on task and project status
            $dueDate = $this->getDueDate($status, $project, $now);

            // Create the task
            $task = Task::create([
                'project_id' => $project->id,
                'title' => 'Task ' . $i . ' for ' . $project->name,
                'description' => 'This is a sample task for the ' . $project->name . ' project.',
                'status' => $status,
                'priority' => $priorities[rand(0, 2)],
                'due_date' => $dueDate,
                'created_by' => $creator->id,
                'created_at' => $now->copy()->subDays(rand(1, 30)),
                'updated_at' => $now->copy()->subDays(rand(0, 5)),
            ]);

            // Assign users to the task - FIXED: Added 'assigned_by' field
            $assigneeCount = rand(1, min(3, count($users)));
            $assignees = $users->random($assigneeCount);

            foreach ($assignees as $user) {
                $task->assignees()->attach($user->id, [
                    'assigned_by' => $creator->id, // Added this line to fix the error
                    'created_at' => $now->copy()->subDays(rand(1, 30)),
                    'updated_at' => $now->copy()->subDays(rand(0, 5)),
                ]);
            }

            // Create task attachments if needed
            // $this->createAttachmentsForTask($task);

            // Create task comments if needed
            // $this->createCommentsForTask($task, $creator, $employees, $now);
        }
    }

    /**
     * Get status distribution based on project status
     *
     * @param string $projectStatus
     * @return array
     */
    private function getStatusDistributionByProjectStatus($projectStatus)
    {
        switch ($projectStatus) {
            case 'completed':
                return [
                    'todo' => 0,
                    'in_progress' => 0,
                    'done' => 100,
                ];
            case 'on_hold':
                return [
                    'todo' => 70,
                    'in_progress' => 20,
                    'done' => 10,
                ];
            case 'active':
                return [
                    'todo' => 30,
                    'in_progress' => 50,
                    'done' => 20,
                ];
            default:
                return [
                    'todo' => 40,
                    'in_progress' => 30,
                    'done' => 30,
                ];
        }
    }

    /**
     * Get task status based on random value and distribution
     *
     * @param int $randomValue
     * @param array $distribution
     * @return string
     */
    private function getStatusFromDistribution($randomValue, $distribution)
    {
        $cumulativePercentage = 0;

        foreach ($distribution as $status => $percentage) {
            $cumulativePercentage += $percentage;

            if ($randomValue <= $cumulativePercentage) {
                return $status;
            }
        }

        return 'todo';
    }

    /**
     * Get due date based on task status and project
     *
     * @param string $taskStatus
     * @param Project $project
     * @param Carbon $now
     * @return string|null
     */
    private function getDueDate($taskStatus, $project, $now)
    {
        // If project has end date, use it as reference
        if ($project->end_date) {
            $projectEndDate = Carbon::parse($project->end_date);

            switch ($taskStatus) {
                case 'done':
                    // Done tasks should have due dates in the past
                    return $now->copy()->subDays(rand(1, 15))->format('Y-m-d');

                case 'in_progress':
                    // In progress tasks should have due dates soon
                    return $now->copy()->addDays(rand(1, 10))->format('Y-m-d');

                case 'todo':
                    // To-do tasks should have due dates in the future but before project end
                    $daysUntilEnd = max(1, $now->diffInDays($projectEndDate));
                    return $now->copy()->addDays(rand(5, $daysUntilEnd))->format('Y-m-d');
            }
        }

        // Default due dates if project has no end date
        switch ($taskStatus) {
            case 'done':
                return $now->copy()->subDays(rand(1, 15))->format('Y-m-d');
            case 'in_progress':
                return $now->copy()->addDays(rand(1, 10))->format('Y-m-d');
            case 'todo':
                return $now->copy()->addDays(rand(5, 30))->format('Y-m-d');
            default:
                return $now->copy()->addDays(rand(1, 30))->format('Y-m-d');
        }
    }
}
