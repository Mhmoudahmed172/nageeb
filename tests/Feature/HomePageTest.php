<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_uses_welcome_and_shows_live_courses_from_the_database(): void
    {
        $teacher = User::factory()->teacher()->create(['name' => 'أ. منى']);
        $teacher->teacherProfile()->create([
            'specialization' => 'كيمياء',
            'is_verified' => true,
        ]);
        Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'كيمياء توجيهي حيّة',
            'status' => CourseStatus::Live,
        ]);
        Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'مسودة لا تظهر في البطل',
            'status' => CourseStatus::Draft,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('كيمياء توجيهي حيّة')
            ->assertSee('أ. منى')
            ->assertDontSee('مسودة لا تظهر في البطل');

        $this->get('/admin/internal/ui-kit')->assertRedirect(route('login'));

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get('/admin/internal/ui-kit')
            ->assertOk()
            ->assertSee('معمل واجهة نجيب');
    }
}
