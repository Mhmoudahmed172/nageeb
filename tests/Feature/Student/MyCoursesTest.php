<?php

namespace Tests\Feature\Student;

use App\Enums\LessonContentType;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyCoursesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_enrollments(): void
    {
        $student = User::factory()->student()->create();
        $active = Course::factory()->create(['title' => 'مادة مفعّلة']);
        $expired = Course::factory()->create(['title' => 'مادة منتهية']);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $active->id,
            'expires_at' => null,
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $expired->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($student)
            ->get(route('student.my-courses.index'))
            ->assertOk()
            ->assertSee('مادة مفعّلة')
            ->assertDontSee('مادة منتهية');
    }

    public function test_show_is_forbidden_without_active_enrollment(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        $this->actingAs($student)
            ->get(route('student.my-courses.show', $course))
            ->assertForbidden();
    }

    public function test_enrolled_student_can_open_lesson_and_comment(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create([
            'unit_id' => $unit->id,
            'title' => 'الدرس الأول',
        ]);
        LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Link,
            'position' => 1,
            'data' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.my-courses.show', $course))
            ->assertOk()
            ->assertSee('الدرس الأول');

        $this->actingAs($student)
            ->post(route('student.my-courses.comments.store', [$course, $lesson]), [
                'message' => 'ما معنى هذه القاعدة؟',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'message' => 'ما معنى هذه القاعدة؟',
            'parent_id' => null,
        ]);
    }
}
