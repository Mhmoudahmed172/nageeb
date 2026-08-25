<?php

namespace Database\Seeders;

use App\Enums\StudentRegion;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@nageeb.test',
            'phone' => '0599000001',
        ]);

        $teacher = User::factory()->teacher()->create([
            'name' => 'معلّم تجريبي',
            'email' => 'teacher@nageeb.test',
            'phone' => '0599000002',
        ]);

        $teacher->teacherProfile()->create([
            'specialization' => 'رياضيات',
            'is_verified' => true,
        ]);

        $student = User::factory()->student()->create([
            'name' => 'طالب تجريبي',
            'email' => 'student@nageeb.test',
            'phone' => '0599000003',
        ]);

        $student->studentProfile()->create([
            'region' => StudentRegion::Gaza,
        ]);
    }
}
