<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeRole       = Role::findByName(
            'employee',
            'web'
        );
        $employeePermission = [
            'Manage Award',
            'Manage Transfer',
            'Manage Resignation',
            'Create Resignation',
            'Edit Resignation',
            'Delete Resignation',
            'Manage Travel',
            'Manage Promotion',
            'Manage Complaint',
            'Create Complaint',
            'Edit Complaint',
            'Delete Complaint',
            'Manage Warning',
            'Create Warning',
            'Edit Warning',
            'Delete Warning',
            'Manage Termination',
            'Manage Employee',
            'Edit Employee',
            'Show Employee',
            'Manage Allowance',
            'Manage Event',
            'Manage Announcement',
            'Manage Leave Type',
            'Manage Leave',
            'Create Leave',
            'Edit Leave',
            'Delete Leave',
            'Manage Meeting',
            'Manage Ticket',
            'Create Ticket',
            'Edit Ticket',
            'Delete Ticket',
            'Manage Language',
            'Manage TimeSheet',
            'Create TimeSheet',
            'Edit TimeSheet',
            'Delete TimeSheet',
            'Manage Attendance',
            'Manage Document',
            'Manage Holiday',
            'Manage Career',
            'Manage Contract',
            'Store Note',
            'Delete Note',
            'Store Comment',
            'Delete Comment',
            'Delete Attachment',
            'Manage Zoom meeting',
            'Show Zoom meeting',
            'Manage Designation',
            'Manage Overtime',
            'Create Overtime',
            'Edit Overtime',
            'Delete Overtime',
            'Manage Reimbursement',
            'Create Reimbursement',
            'Manage Project',
            'Manage Task',
            'Create Task',
            'Edit Task',
            'Manage Pay Slip',
        ];

        $employeeRole->givePermissionTo($employeePermission);
        $userEmployee = User::create(
            [
                'first_name' => 'employee2',
                'last_name' => 'test2',
                'email' => 'employee.test2@example.com',
                'password' => Hash::make('password'),
                'type' => 'employee',
                'lang' => 'en',
                'avatar'    => '',
                'created_by' => 2,
                'email_verified_at' => date("Y-m-d H:i:s"),
            ]
        );
        $userEmployee->assignRole($employeeRole);

        $latest = Employee::where('created_by', '=', 2)->latest('id')->first();
        if (!$latest) {
            $latest = 1;
        } else {
            $latest = $latest->id + 1;
        }


        Employee::create(
            [
                'user_id' => $userEmployee->id,
                'name' => $userEmployee->first_name . ' ' . $userEmployee->last_name,
                'dob' => date("Y-m-d"),
                'gender' => 'Male',
                'phone' => '081392223993',
                'address' => 'Jakarta, Indonesia',
                'email' => 'employee.test2@example.com',
                'password' => Hash::make('password'),
                'employee_id' => $latest,
                // 'biometric_emp_id' => !empty($request['biometric_emp_id']) ? $request['biometric_emp_id'] : '',
                'branch_id' => 1,
                'department_id' => 1,
                'designation_id' => 1,
                'company_doj' => date("Y-m-d"),
                // 'documents' => $document_implode,
                'account_holder_name' => 'employee test',
                'account_number' => '123443132',
                'bank_name' => 'Bank Central Asia',
                'bank_identifier_code' => 'CENAIDAJA',
                'branch_location' => 'Jakarta',
                // 'tax_payer_id' => $request['tax_payer_id'],
                'created_by' => 2,
                'salary_type' => 'monthly',
                'salary' => '4000000',
            ]
        );
    }
}
