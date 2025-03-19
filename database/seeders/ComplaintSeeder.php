<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employees = Employee::all();

        // Make sure we have enough employees for our seeder
        if ($employees->count() < 2) {
            $this->command->info('Not enough employees found. Please run EmployeeSeeder first.');
            return;
        }

        $createdBy = $employees->first()->created_by;

        $complaintsData = [
            [
                'complaint_from' => $employees[0]->id,
                'complaint_against' => $employees[1]->id,
                'title' => 'Unprofessional Behavior',
                'complaint_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'description' => 'The employee has been consistently late to team meetings and uses unprofessional language during discussions.',
                'created_by' => $createdBy,
            ],
            [
                'complaint_from' => $employees[1]->id,
                'complaint_against' => $employees[0]->id,
                'title' => 'Missing Project Deadlines',
                'complaint_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'description' => 'Team member has missed three consecutive project deadlines without proper communication.',
                'created_by' => $createdBy,
            ],
        ];

        foreach ($complaintsData as $complaint) {
            Complaint::create($complaint);
        }

        $this->command->info('Complaint table seeded successfully!');
    }
}
