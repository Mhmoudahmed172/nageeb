<?php

namespace Tests\Feature\Auth;

use App\Enums\StudentRegion;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboards_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/teacher/dashboard')->assertRedirect('/login');
        $this->get('/student/dashboard')->assertRedirect('/login');
    }

    public function test_student_registration_creates_profile_and_redirects(): void
    {
        $response = $this->post('/register/student', [
            'name' => 'أحمد محمد',
            'email' => 'ahmad@example.com',
            'phone' => '0599123456',
            'region' => StudentRegion::Gaza->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('student.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@example.com',
            'role' => UserRole::Student->value,
        ]);

        $user = User::where('email', 'ahmad@example.com')->first();
        $this->assertNotNull($user->studentProfile);
        $this->assertSame(StudentRegion::Gaza, $user->studentProfile->region);
    }

    public function test_teacher_registration_sets_is_verified_false(): void
    {
        $response = $this->post('/register/teacher', [
            'name' => 'سارة علي',
            'email' => 'sara@example.com',
            'phone' => '0599765432',
            'specialization' => 'فيزياء',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));

        $user = User::where('email', 'sara@example.com')->first();
        $this->assertTrue($user->isTeacher());
        $this->assertFalse($user->teacherProfile->is_verified);
        $this->assertSame('فيزياء', $user->teacherProfile->specialization);
    }

    public function test_login_redirects_by_role(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@test.com']);
        $teacher = User::factory()->teacher()->create(['email' => 'teacher@test.com']);
        $student = User::factory()->student()->create(['email' => 'student@test.com']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $this->post('/login', ['email' => $teacher->email, 'password' => 'password'])
            ->assertRedirect(route('teacher.dashboard'));

        $this->post('/logout');

        $this->post('/login', ['email' => $student->email, 'password' => 'password'])
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_user_cannot_access_other_role_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/teacher/dashboard')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk();
    }
}
