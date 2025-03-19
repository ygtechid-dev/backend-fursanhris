<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Termination;
use App\Models\TerminationType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TerminateEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get employee with ID 1
        $employee = Employee::where('id', 1)->first();

        // If employee doesn't exist, log a message and return
        if (!$employee) {
            $this->command->error('Employee with ID 1 not found or user is not an employee.');
            return;
        }

        // Get the company ID (creator ID)
        $companyId = $employee->created_by;

        // Find a company admin to be set as the one who terminated the employee
        $adminUser = User::where('id', $companyId)
            ->first();

        if (!$adminUser) {
            $this->command->error('No admin user found to perform termination.');
            return;
        }

        // Check if employee is already terminated
        if ($employee->user->isTerminated()) {
            $this->command->info('Employee is already terminated.');
            return;
        }

        try {
            DB::beginTransaction();

            $termin_type = TerminationType::create([
                'name'  => 'Voluntary Termination',
                'created_by'    => $employee->user->creatorId()
            ]);

            // Create a termination record
            $termination = Termination::create([
                'user_id' => $employee->user->id,
                'employee_id' => $employee->id ?? null,
                'termination_type_id' => $termin_type->id,
                'termination_date' => now(),
                'description' => 'Terminated as part of system testing',
                'reason' => 'Testing termination functionality',
                'notice_date' => now()->subDays(14), // 14 days notice
                'terminated_by' => $adminUser->id,
                'is_mobile_access_allowed' => true, // Allow mobile access
                'status' => 'active',
                'company_id' => $companyId,
                'documents' => null,
                'created_by' => $employee->user->creatorId(),
            ]);

            // Update user to reflect terminated status
            $employee->user->is_login_enable = true; // Keep login enabled since mobile access is allowed
            $employee->user->save();

            DB::commit();

            $this->command->info('Employee with ID ' . $employee->id . ' (' . $employee->user->employee_name() . ') has been successfully terminated.');
            $this->command->info('Termination ID: ' . $termination->id);
            $this->command->info('Employee is allowed to access via mobile app only.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error terminating employee: ' . $e->getMessage());
        }
    }
}
