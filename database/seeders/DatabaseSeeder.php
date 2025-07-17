<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // create user admin
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_name' => 'admin', // Set role_name to 'admin'
        ]);
        // create user operator
        User::create([
            'name' => 'Sandi',
            'username' => 'sandi',
            'email' => 'sandi@gmail.com',
            'password' => bcrypt('password'),
            'role_name' => 'operator', // Set role_name to 'operator'
        ]);
    }
}
