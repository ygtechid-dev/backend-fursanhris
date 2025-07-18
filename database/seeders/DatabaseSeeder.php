<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsersTableSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(LeaveTypeSeeder::class);
        $this->call(DesignationSeeder::class);
        $this->call(EventTableSeeder::class);
        $this->call(ReimbursementSeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(TaskSeeder::class);
        $this->call(AllowanceDeductionSeeder::class);
        // $this->call(PayslipSeeder::class);
        $this->call(RewardSeeder::class);
        $this->call(TripSeeder::class);
        $this->call(PromotionSeeder::class);
        $this->call(ComplaintSeeder::class);
        $this->call(WarningSeeder::class);
        $this->call(TerminationTypeSeeder::class);
        $this->call(AssetSeeder::class);

        /** Optional Seeder */
        $this->call(ResignationSeeder::class);
        $this->call(TerminateEmployeeSeeder::class); // harus di update fieldnya karena modelnya ada yang berubah
    }
}
