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
        // User::factory(10)->create();

        // User::create([
        //     'first_name' => 'Test',
        //     'last_name' => 'User',
        //     'password' => Hash::make('password'),
        //     'username' => 'tester',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(UsersTableSeeder::class);
    }
}
