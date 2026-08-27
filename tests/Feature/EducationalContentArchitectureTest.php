<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\SubscriptionPackage;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationalContentArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_owns_course_and_cannot_modify_another_teachers_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $owned = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'مادتي',
        ]);
        $foreign = Course::factory()->create([
            'title' => 'مادة معلم آخر',
        ]);

        $this->assertTrue($owned->isOwnedBy($teacher->id));
        $this->assertFalse($foreign->isOwnedBy($teacher->id));

        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $foreign), [
                'title' => 'محاولة تعديل',
                'description' => 'لا',
                'grade_level' => 'توجيهي',
                'status' => 'live',
                'is_free' => '0',
            ])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.destroy', $foreign))
            ->assertForbidden();

        $this->assertDatabaseHas('courses', [
            'id' => $foreign->id,
            'title' => 'مادة معلم آخر',
        ]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $owned), [
                'title' => 'مادة محدّثة',
                'description' => $owned->description,
                'grade_level' => $owned->grade_level->value,
                'status' => 'live',
                'is_free' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'id' => $owned->id,
            'title' => 'مادة محدّثة',
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_course_has_units_unit_has_lessons_and_lesson_belongs_to_exactly_one_unit(): void
    {
        $course = Course::factory()->create();
        $unitA = Unit::factory()->forCourse($course)->create([
            'title' => 'الوحدة أ',
            'position' => 1,
        ]);
        $unitB = Unit::factory()->forCourse($course)->create([
            'title' => 'الوحدة ب',
            'position' => 2,
        ]);
        $lesson = Lesson::factory()->create([
            'unit_id' => $unitA->id,
            'title' => 'درس واحد',
            'position' => 1,
        ]);
        Lesson::factory()->create([
            'unit_id' => $unitB->id,
            'title' => 'درس الوحدة ب',
            'position' => 1,
        ]);

        $this->assertCount(2, $course->units);
        $this->assertTrue($course->units->contains($unitA));
        $this->assertEquals($unitA->id, $lesson->unit_id);
        $this->assertTrue($lesson->unit->is($unitA));
        $this->assertFalse($lesson->unit->is($unitB));
        $this->assertSame(1, $lesson->unit()->count());
        $this->assertCount(1, $unitA->lessons);
        $this->assertTrue($unitA->lessons->contains($lesson));
        $this->assertFalse($unitB->lessons->contains($lesson));
    }

    public function test_lesson_can_have_multiple_content_blocks(): void
    {
        $lesson = Lesson::factory()->create();

        LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Text,
            'position' => 1,
            'data' => ['body' => 'شرح مكتوب'],
            'status' => ContentStatus::Live,
        ]);
        LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Video,
            'position' => 2,
            'data' => ['path' => 'lessons/demo.mp4', 'source' => 'upload'],
        ]);
        LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Quiz,
            'position' => 3,
            'data' => ['questions' => []],
        ]);

        $this->assertCount(3, $lesson->contents()->get());
        $this->assertEquals(
            [
                LessonContentType::Text,
                LessonContentType::Video,
                LessonContentType::Quiz,
            ],
            $lesson->contents()->orderBy('position')->get()->pluck('type')->all(),
        );
    }

    public function test_packages_belong_to_courses_not_units(): void
    {
        $course = Course::factory()->create();
        $package = SubscriptionPackage::factory()->create([
            'course_id' => $course->id,
            'name' => 'المادة كاملة',
        ]);

        $this->assertTrue($package->course->is($course));
        $this->assertTrue($course->packages->contains($package));
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasColumn('subscription_packages', 'unit_id'),
        );
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasColumn('units', 'package_id'),
        );
    }
}
