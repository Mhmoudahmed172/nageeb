<?php

namespace Tests\Feature\Teacher;

use App\Enums\CourseStatus;
use App\Enums\GradeLevel;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherCourseFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_create_course_form(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.create'))
            ->assertOk()
            ->assertSee('إضافة مادة')
            ->assertSee('تفاصيل المادة')
            ->assertSee('إعدادات المادة')
            ->assertSee('حفظ ومتابعة');
    }

    public function test_teacher_creates_course_for_self_and_is_sent_to_units_section(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), [
                'title' => 'الفيزياء — الصف الحادي عشر',
                'description' => 'وصف المادة',
                'grade_level' => GradeLevel::Eleventh->value,
                'status' => CourseStatus::Live->value,
                'is_free' => '1',
                'reference_price' => '80',
                'teacher_id' => $other->id,
                'save_action' => 'continue',
                'cover_image' => UploadedFile::fake()->image('cover.jpg', 900, 1200),
            ])
            ->assertRedirect();

        $course = Course::query()->where('title', 'الفيزياء — الصف الحادي عشر')->first();

        $this->assertNotNull($course);
        $this->assertSame($teacher->id, $course->teacher_id);
        $this->assertTrue($course->is_free);
        $this->assertSame('80.00', (string) $course->reference_price);
        $this->assertNotNull($course->cover_image);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), [
                'title' => 'مادة للمتابعة',
                'grade_level' => GradeLevel::Twelfth->value,
                'status' => CourseStatus::Live->value,
                'is_free' => '0',
                'save_action' => 'continue',
            ])
            ->assertRedirect(route('teacher.courses.content', Course::query()->where('title', 'مادة للمتابعة')->first()));
    }

    public function test_save_as_draft_forces_draft_status(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), [
                'title' => 'مسودة مادة',
                'grade_level' => GradeLevel::Tenth->value,
                'status' => CourseStatus::Live->value,
                'is_free' => '0',
                'save_action' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'title' => 'مسودة مادة',
            'status' => CourseStatus::Draft->value,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_create_course_requires_title_and_grade(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->from(route('teacher.courses.create'))
            ->post(route('teacher.courses.store'), [
                'title' => '',
                'grade_level' => '',
                'status' => CourseStatus::Draft->value,
                'is_free' => '0',
            ])
            ->assertRedirect(route('teacher.courses.create'))
            ->assertSessionHasErrors(['title', 'grade_level']);
    }

    public function test_teacher_cannot_edit_another_teachers_course_form(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreign = Course::factory()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $foreign))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $foreign), [
                'title' => 'اختراق',
                'grade_level' => GradeLevel::Tenth->value,
                'status' => CourseStatus::Live->value,
                'is_free' => '0',
            ])
            ->assertForbidden();
    }

    public function test_teacher_can_update_own_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $course), [
                'title' => 'عنوان محدّث',
                'description' => 'وصف محدّث',
                'grade_level' => GradeLevel::Twelfth->value,
                'status' => CourseStatus::Archived->value,
                'is_free' => '1',
                'reference_price' => '120',
                'save_action' => 'continue',
            ])
            ->assertRedirect(route('teacher.courses.content', $course));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'عنوان محدّث',
            'is_free' => 1,
            'status' => CourseStatus::Archived->value,
        ]);
    }
}
