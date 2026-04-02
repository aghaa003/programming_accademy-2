<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Seed roles
        DB::table('roles')->insertOrIgnore([
            ['name' => 'student'],
            ['name' => 'admin'],
        ]);

        // Admin user
        $admin = User::factory()->create([
            'firstName' => 'Admin',
            'lastName'  => 'User',
            'email'     => 'admin@academy.test',
            'username'  => 'admin_seed',
        ]);
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        DB::table('user_roles')->insert(['user_id' => $admin->id, 'role_id' => $adminRoleId]);

        // Student user
        $student = User::factory()->create([
            'firstName' => 'Student',
            'lastName'  => 'User',
            'email'     => 'student@academy.test',
            'username'  => 'student_seed',
        ]);
        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id');
        DB::table('user_roles')->insert(['user_id' => $student->id, 'role_id' => $studentRoleId]);
    }
}
