<?php

namespace Tests\Feature\Teacher;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherLessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_show_lesson_form_when_course_has_no_units(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.lessons.create', $course))
            ->assertOk()
            ->assertSee('يجب إنشاء وحدة أولًا', false)
            ->assertSee('أضف وحدة إلى هذه المادة قبل إضافة الدروس.', false)
            ->assertDontSee('عنوان الدرس');
    }

    public function test_teacher_can_create_a_lesson_in_a_course_unit(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create(['position' => 1]);

        $response = $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.store', $course), [
                'title' => 'الدرس الأول',
                'description' => 'شرح',
                'unit_id' => $unit->id,
                'status' => ContentStatus::Live->value,
                'is_preview' => '1',
                'save_action' => 'save',
                'videos' => [UploadedFile::fake()->create('intro.mp4', 200, 'video/mp4')],
                'attachments' => [UploadedFile::fake()->create('notes.pdf', 80, 'application/pdf')],
            ])
            ->assertRedirect();

        $lesson = Lesson::query()->where('title', 'الدرس الأول')->first();
        $this->assertNotNull($lesson);
        $response->assertRedirect(route('teacher.courses.lessons.edit', [$course, $lesson]));
        $this->assertSame($unit->id, $lesson->unit_id);
        $this->assertTrue($lesson->is_preview);
        $this->assertSame(1, $lesson->position);
        $this->assertEquals(2, $lesson->contents()->count());
        $this->assertTrue($lesson->contents()->where('type', LessonContentType::Video)->exists());
        $this->assertTrue($lesson->contents()->where('type', LessonContentType::File)->exists());
    }

    public function test_save_as_draft_forces_draft_status(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create(['position' => 1]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.store', $course), [
                'title' => 'مسودة درس',
                'unit_id' => $unit->id,
                'status' => ContentStatus::Live->value,
                'is_preview' => '0',
                'save_action' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lessons', [
            'title' => 'مسودة درس',
            'status' => ContentStatus::Draft->value,
            'unit_id' => $unit->id,
        ]);
    }

    public function test_teacher_can_update_a_lesson_and_move_it_between_units_in_the_same_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unitA = Unit::factory()->forCourse($course)->create(['title' => 'الوحدة الأولى', 'position' => 1]);
        $unitB = Unit::factory()->forCourse($course)->create(['title' => 'الوحدة الثانية', 'position' => 2]);
        $lesson = Lesson::factory()->create([
            'unit_id' => $unitA->id,
            'title' => 'قديم',
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.lessons.update', [$course, $lesson]), [
                'title' => 'الدرس المنقول',
                'description' => 'بعد النقل',
                'unit_id' => $unitB->id,
                'status' => ContentStatus::Live->value,
                'is_preview' => '0',
                'save_action' => 'save',
            ])
            ->assertRedirect(route('teacher.courses.lessons.edit', [$course, $lesson]));

        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'title' => 'الدرس المنقول',
            'unit_id' => $unitB->id,
            'position' => 1,
        ]);
    }

    public function test_cannot_assign_a_unit_from_another_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        Unit::factory()->forCourse($course)->create(['position' => 1]);
        $foreignUnit = Unit::factory()->create(['position' => 1]);

        $this->actingAs($teacher)
            ->from(route('teacher.courses.lessons.create', $course))
            ->post(route('teacher.courses.lessons.store', $course), [
                'title' => 'درس غير صالح',
                'unit_id' => $foreignUnit->id,
                'status' => ContentStatus::Draft->value,
                'is_preview' => '0',
            ])
            ->assertRedirect(route('teacher.courses.lessons.create', $course))
            ->assertSessionHasErrors('unit_id');

        $this->assertDatabaseMissing('lessons', ['title' => 'درس غير صالح']);
    }

    public function test_unauthorized_teacher_cannot_manage_lessons(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreignCourse = Course::factory()->create();
        $foreignUnit = Unit::factory()->forCourse($foreignCourse)->create(['position' => 1]);
        $foreignLesson = Lesson::factory()->create(['unit_id' => $foreignUnit->id, 'position' => 1]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.lessons.create', $foreignCourse))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.store', $foreignCourse), [
                'title' => 'اختراق',
                'unit_id' => $foreignUnit->id,
                'status' => ContentStatus::Draft->value,
                'is_preview' => '0',
            ])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.lessons.edit', [$foreignCourse, $foreignLesson]))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->put(route('teacher.courses.lessons.update', [$foreignCourse, $foreignLesson]), [
                'title' => 'اختراق',
                'unit_id' => $foreignUnit->id,
                'status' => ContentStatus::Live->value,
                'is_preview' => '0',
            ])
            ->assertForbidden();
    }

    public function test_cannot_edit_a_lesson_through_another_course_url(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        Unit::factory()->forCourse($course)->create(['position' => 1]);
        $otherCourse = Course::factory()->create(['teacher_id' => $teacher->id]);
        $otherUnit = Unit::factory()->forCourse($otherCourse)->create(['position' => 1]);
        $lesson = Lesson::factory()->create(['unit_id' => $otherUnit->id, 'position' => 1]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.lessons.edit', [$course, $lesson]))
            ->assertNotFound();
    }
}
