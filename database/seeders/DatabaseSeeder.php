<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DEFAULT_PASSWORD = '12345678';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $department = Department::query()->firstOrCreate(
            ['name' => 'Computer Science'],
            ['description' => 'Department of Computer Science']
        );

        $program = Program::query()->firstOrCreate(
            ['name' => 'Software Engineering'],
            [
                'description' => 'Graduate software engineering program',
                'department_id' => $department->id,
            ]
        );

        Course::query()->firstOrCreate(
            ['name' => 'Algorithms'],
            [
                'description' => 'Introduction to algorithms',
                'program_id' => $program->id,
            ]
        );

        

        User::query()->updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'first_name' => 'Student',
                'last_name' => 'User',
                'name' => 'Student User',
                'national_id' => '11111111111111',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => UserRole::Student,
            ]
        );

        User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'national_id' => '22222222222222',
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'role' => UserRole::Student,
        ]);
    }
}
