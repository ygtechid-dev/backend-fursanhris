<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveType::create([
            'title'         => 'Medical Leave',
            'days'          => 10, // days / year
            'created_by'    => 2,
        ]);
        LeaveType::create([
            'title'         => 'Casual Leave',
            'days'          => 6, // days / year
            'created_by'    => 2,
        ]);
    }
}
