<?php

namespace Tests\Feature\Teacher;

use App\Enums\ContentStatus;
use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_shows_header_tabs_and_syllabus(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'اللغة الإنجليزية — توجيهي',
            'status' => CourseStatus::Draft,
        ]);
        $unit = Unit::factory()->forCourse($course)->create([
            'title' => 'الوحدة الأولى',
            'position' => 1,
        ]);
        Lesson::factory()->create(['unit_id' => $unit->id, 'title' => 'مقدمة', 'position' => 1]);
        Lesson::factory()->create(['unit_id' => $unit->id, 'title' => 'الدرس الأول', 'position' => 2]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $course))
            ->assertOk()
            ->assertSee('اللغة الإنجليزية — توجيهي')
            ->assertSee('نظرة عامة')
            ->assertSee('المحتوى')
            ->assertSee('خطط الوصول والأسعار')
            ->assertSee('معاينة')
            ->assertSee('تعديل المادة')
            ->assertSee('نشر')
            ->assertSee('الوحدة الأولى')
            ->assertSee('درسان')
            ->assertSee('مقدمة')
            ->assertSee('+ إضافة درس')
            ->assertSee('+ إضافة وحدة');
    }

    public function test_teacher_can_publish_and_unpublish_own_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'status' => CourseStatus::Draft,
        ]);

        $this->actingAs($teacher)
            ->from(route('teacher.courses.content', $course))
            ->post(route('teacher.courses.publish', $course))
            ->assertRedirect();

        $this->assertSame(CourseStatus::Live, $course->fresh()->status);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.publish', $course));

        $this->assertSame(CourseStatus::Draft, $course->fresh()->status);
    }

    public function test_teacher_can_duplicate_reorder_relocate_and_delete_lessons(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unitA = Unit::factory()->forCourse($course)->create(['title' => 'الوحدة الأولى', 'position' => 1]);
        $unitB = Unit::factory()->forCourse($course)->create(['title' => 'الوحدة الثانية', 'position' => 2]);
        $first = Lesson::factory()->create(['unit_id' => $unitA->id, 'title' => 'مقدمة', 'position' => 1]);
        $second = Lesson::factory()->create(['unit_id' => $unitA->id, 'title' => 'الدرس الأول', 'position' => 2]);
        LessonContent::factory()->create(['lesson_id' => $first->id, 'position' => 1]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.move', [$course, $second]), ['direction' => 'up'])
            ->assertRedirect(route('teacher.courses.content', $course));

        $this->assertSame(1, $second->fresh()->position);
        $this->assertSame(2, $first->fresh()->position);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.duplicate', [$course, $first]))
            ->assertRedirect();

        $this->assertDatabaseHas('lessons', [
            'unit_id' => $unitA->id,
            'title' => 'نسخة من مقدمة',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.relocate', [$course, $first]), [
                'unit_id' => $unitB->id,
            ])
            ->assertRedirect();

        $this->assertSame($unitB->id, $first->fresh()->unit_id);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.lessons.destroy', [$course, $second]))
            ->assertRedirect();

        $this->assertDatabaseMissing('lessons', ['id' => $second->id]);
    }

    public function test_unauthorized_teacher_cannot_open_workspace(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreign = Course::factory()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $foreign))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.overview', $foreign))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.publish', $foreign))
            ->assertForbidden();
    }
}
