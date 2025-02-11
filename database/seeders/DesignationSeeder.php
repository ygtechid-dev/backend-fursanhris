<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name'        => 'Indonesia',
            'created_by'  => 2,
        ]);
        Branch::create([
            'name'        => 'Malaysia',
            'created_by'  => 2,
        ]);

        Department::create([
            'branch_id'   => 1,
            'name'        => 'Technology',
            'created_by'  => 2,
        ]);
        Department::create([
            'branch_id'   => 2,
            'name'        => 'Finance',
            'created_by'  => 2,
        ]);

        Designation::create([
            'branch_id'   => 1,
            'department_id'   => 1,
            'name'        => 'Developers',
            'created_by'  => 2,
        ]);
        Designation::create([
            'branch_id'   => 2,
            'department_id'   => 2,
            'name'        => 'Office Boy',
            'created_by'  => 2,
        ]);
    }
}
