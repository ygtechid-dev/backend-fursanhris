<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Warning;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Make sure we have employees in the database
        $employees = Employee::all();

        // If no employees, we can't create warnings
        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        // Clear existing warnings
        DB::table('warnings')->truncate();

        // Sample warning subjects
        $subjects = [
            'Unexcused Absence',
            'Poor Performance',
            'Violation of Company Policy',
            'Late Arrival',
            'Unprofessional Behavior',
            'Misuse of Company Property',
            'Failure to Complete Tasks',
            'Insubordination',
        ];

        // Sample warning descriptions
        $descriptions = [
            'Employee was absent without prior approval or valid reason.',
            'Employee failed to meet performance standards for the third consecutive month.',
            'Employee violated the company\'s code of conduct by [specific violation].',
            'Employee has been late to work more than 5 times this month.',
            'Employee displayed unprofessional behavior towards colleagues during the team meeting.',
            'Employee was found misusing company equipment for personal purposes.',
            'Employee consistently fails to complete assigned tasks by the deadline.',
            'Employee refused to follow direct instructions from their supervisor.',
        ];

        // Create 20 sample warnings
        for ($i = 0; $i < 2; $i++) {
            $warningTo = $employees->random();

            // Make sure warning_by is different from warning_to
            do {
                $warningBy = $employees->random();
            } while ($warningBy->id === $warningTo->id);

            $subjectIndex = array_rand($subjects);

            Warning::create([
                'warning_to' => $warningTo->id,
                'warning_by' => $warningBy->id,
                'subject' => $subjects[$subjectIndex],
                'description' => $descriptions[$subjectIndex],
                'warning_date' => Carbon::now()->subDays(rand(1, 90))->format('Y-m-d'),
                'created_by' => $warningBy->created_by,
            ]);
        }

        $this->command->info('Warning table seeded successfully!');
    }
}
