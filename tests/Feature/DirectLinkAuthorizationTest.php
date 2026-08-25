<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectLinkAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_open_a_course_they_are_not_enrolled_in(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        $this->actingAs($student)
            ->get(route('student.my-courses.show', $course))
            ->assertForbidden()
            ->assertSee('غير مصرّح لك بالوصول', false);
    }

    public function test_student_can_open_an_enrolled_course(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.my-courses.show', $course))
            ->assertOk();
    }

    public function test_teacher_cannot_open_another_teachers_course_by_changing_id(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreignCourse = Course::factory()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.edit', $foreignCourse))
            ->assertForbidden()
            ->assertSee('غير مصرّح لك بالوصول', false);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.packages.index', $foreignCourse))
            ->assertForbidden();
    }

    public function test_teacher_can_open_own_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.edit', $course))
            ->assertOk();
    }
}
