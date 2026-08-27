<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\AccessPlan;
use App\Models\AccessPlanPrice;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Region;
use App\Models\Semester;
use App\Models\Unit;
use App\Models\User;
use App\Support\ContentAccess;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_read_or_modify_another_teachers_course_or_nested_ids(): void
    {
        $teacherA = User::factory()->teacher()->create();
        $teacherB = User::factory()->teacher()->create();
        $courseB = Course::factory()->create(['teacher_id' => $teacherB->id]);
        $semesterB = $courseB->defaultSemester();
        $unitB = Unit::factory()->forCourse($courseB)->create();
        $lessonB = Lesson::factory()->create(['unit_id' => $unitB->id]);

        $this->actingAs($teacherA)
            ->get(route('teacher.courses.edit', $courseB))
            ->assertForbidden();

        $this->actingAs($teacherA)
            ->put(route('teacher.courses.update', $courseB), [
                'title' => 'اختراق',
                'description' => 'x',
                'grade_level' => 'عاشر',
                'status' => 'live',
                'is_free' => '0',
            ])
            ->assertForbidden();

        $this->actingAs($teacherA)
            ->get(route('teacher.courses.units.edit', [$courseB, $unitB]))
            ->assertForbidden();

        $this->actingAs($teacherA)
            ->get(route('teacher.courses.lessons.edit', [$courseB, $lessonB]))
            ->assertForbidden();

        $courseA = Course::factory()->create(['teacher_id' => $teacherA->id]);
        $this->actingAs($teacherA)
            ->get(route('teacher.courses.units.edit', [$courseA, $unitB]))
            ->assertNotFound();

        $this->actingAs($teacherA)
            ->put(route('teacher.courses.semesters.update', [$courseA, $semesterB]), [
                'title' => 'فصل مسروق',
                'status' => ContentStatus::Live->value,
            ])
            ->assertNotFound();
    }

    public function test_hierarchy_course_semester_unit_lesson_and_units_cannot_leave_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $semester = Semester::factory()->create([
            'course_id' => $course->id,
            'title' => 'الفصل الأول',
            'position' => 2,
        ]);
        $unit = Unit::factory()->create([
            'semester_id' => $semester->id,
            'title' => 'الوحدة الأولى',
            'position' => 1,
        ]);
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id, 'title' => 'درس']);

        $this->assertTrue($unit->belongsToCourse($course));
        $this->assertTrue($lesson->belongsToCourse($course));
        $this->assertTrue($course->semesters->contains($semester));

        $other = Course::factory()->create(['teacher_id' => $teacher->id]);
        $foreignUnit = Unit::factory()->forCourse($other)->create();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.store', $course), [
                'title' => 'درس غير صالح',
                'unit_id' => $foreignUnit->id,
                'status' => ContentStatus::Live->value,
                'is_preview' => '0',
                'region_scope' => 'all',
            ])
            ->assertSessionHasErrors('unit_id');
    }

    public function test_access_plans_only_select_own_semesters_and_course_can_have_many(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $other = Course::factory()->create(['teacher_id' => $teacher->id]);
        $gaza = $this->regionId();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.packages.store', $course), [
                'title' => 'خطة',
                'status' => ContentStatus::Live->value,
                'semester_ids' => [$other->defaultSemester()->id],
                'prices' => [['region_id' => $gaza, 'price' => 10]],
            ])
            ->assertSessionHasErrors('semester_ids.0');

        AccessPlan::factory()->create(['course_id' => $course->id, 'title' => 'أ']);
        AccessPlan::factory()->create(['course_id' => $course->id, 'title' => 'ب']);
        $this->assertSame(2, $course->accessPlans()->count());
    }

    public function test_regional_pricing_unique_per_plan_and_region(): void
    {
        $plan = AccessPlan::factory()->create();
        $gaza = Region::query()->where('code', 'gaza')->first();
        $west = Region::query()->where('code', 'west_bank')->first();

        AccessPlanPrice::factory()->create([
            'access_plan_id' => $plan->id,
            'region_id' => $gaza->id,
            'price' => 100,
        ]);
        AccessPlanPrice::factory()->create([
            'access_plan_id' => $plan->id,
            'region_id' => $west->id,
            'price' => 130,
        ]);

        $this->assertSame(2, $plan->prices()->count());

        $this->expectException(QueryException::class);
        AccessPlanPrice::factory()->create([
            'access_plan_id' => $plan->id,
            'region_id' => $gaza->id,
            'price' => 999,
        ]);
    }

    public function test_content_region_rules_and_semester_plan_access(): void
    {
        $access = app(ContentAccess::class);
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $s1 = $course->defaultSemester();
        $s1->update(['title' => 'الفصل الأول']);
        $s2 = Semester::factory()->create([
            'course_id' => $course->id,
            'title' => 'الفصل الثاني',
            'position' => 2,
        ]);

        $unit1 = Unit::factory()->create(['semester_id' => $s1->id, 'position' => 1]);
        $unit2 = Unit::factory()->create(['semester_id' => $s2->id, 'position' => 1]);
        $lesson1 = Lesson::factory()->create(['unit_id' => $unit1->id, 'status' => ContentStatus::Live]);
        Lesson::factory()->create(['unit_id' => $unit2->id, 'status' => ContentStatus::Live]);

        $open = LessonContent::factory()->create([
            'lesson_id' => $lesson1->id,
            'type' => LessonContentType::Text,
            'status' => ContentStatus::Live,
        ]);
        $gazaOnly = LessonContent::factory()->create([
            'lesson_id' => $lesson1->id,
            'type' => LessonContentType::Text,
            'status' => ContentStatus::Live,
            'position' => 2,
        ]);
        $westOnly = LessonContent::factory()->create([
            'lesson_id' => $lesson1->id,
            'type' => LessonContentType::Text,
            'status' => ContentStatus::Live,
            'position' => 3,
        ]);
        $both = LessonContent::factory()->create([
            'lesson_id' => $lesson1->id,
            'type' => LessonContentType::Text,
            'status' => ContentStatus::Live,
            'position' => 4,
        ]);

        $gaza = Region::query()->where('code', 'gaza')->first();
        $west = Region::query()->where('code', 'west_bank')->first();
        $gazaOnly->regions()->attach($gaza->id);
        $westOnly->regions()->attach($west->id);
        $both->regions()->sync([$gaza->id, $west->id]);

        $planS1 = AccessPlan::factory()->create(['course_id' => $course->id, 'title' => 'فصل 1']);
        $planS1->semesters()->attach($s1->id);
        $planFull = AccessPlan::factory()->create(['course_id' => $course->id, 'title' => 'كاملة']);
        $planFull->semesters()->sync([$s1->id, $s2->id]);

        $gazaStudent = User::factory()->student()->create();
        $gazaStudent->studentProfile()->create(['region_id' => $gaza->id]);
        $westStudent = User::factory()->student()->create();
        $westStudent->studentProfile()->create(['region_id' => $west->id]);

        Enrollment::factory()->create([
            'student_id' => $gazaStudent->id,
            'course_id' => $course->id,
            'access_plan_id' => $planS1->id,
            'status' => 'active',
        ]);
        Enrollment::factory()->create([
            'student_id' => $westStudent->id,
            'course_id' => $course->id,
            'access_plan_id' => $planFull->id,
            'status' => 'active',
        ]);

        $this->assertTrue($access->studentCanAccessSemester($gazaStudent, $s1));
        $this->assertFalse($access->studentCanAccessSemester($gazaStudent, $s2));
        $this->assertTrue($access->studentCanAccessSemester($westStudent, $s2));

        $this->assertTrue($access->studentCanAccessContent($gazaStudent, $open));
        $this->assertTrue($access->studentCanAccessContent($westStudent, $open));
        $this->assertTrue($access->studentCanAccessContent($gazaStudent, $gazaOnly));
        $this->assertFalse($access->studentCanAccessContent($westStudent, $gazaOnly));
        $this->assertFalse($access->studentCanAccessContent($gazaStudent, $westOnly));
        $this->assertTrue($access->studentCanAccessContent($westStudent, $westOnly));
        $this->assertTrue($access->studentCanAccessContent($gazaStudent, $both));
        $this->assertTrue($access->studentCanAccessContent($westStudent, $both));

        $expiredStudent = User::factory()->student()->create();
        $expiredStudent->studentProfile()->create(['region_id' => $gaza->id]);
        Enrollment::factory()->create([
            'student_id' => $expiredStudent->id,
            'course_id' => $course->id,
            'access_plan_id' => $planFull->id,
            'expires_at' => now()->subDay(),
            'status' => 'active',
        ]);
        $this->assertFalse($access->studentCanAccessCourse($expiredStudent, $course));
        $this->assertFalse($access->studentCanAccessContent($expiredStudent, $open));
    }

    public function test_multiple_enrollments_on_the_same_course_are_combined(): void
    {
        $access = app(ContentAccess::class);
        $course = Course::factory()->create();
        $s1 = $course->defaultSemester();
        $s2 = Semester::factory()->create(['course_id' => $course->id, 'title' => 'الفصل الثاني']);
        $plan1 = AccessPlan::factory()->create(['course_id' => $course->id]);
        $plan1->semesters()->attach($s1->id);
        $plan2 = AccessPlan::factory()->create(['course_id' => $course->id]);
        $plan2->semesters()->attach($s2->id);

        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId()]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan1->id,
            'status' => 'active',
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan2->id,
            'status' => 'active',
        ]);

        $this->assertTrue($access->studentCanAccessSemester($student, $s1));
        $this->assertTrue($access->studentCanAccessSemester($student, $s2));
    }

    public function test_enrollment_price_snapshot_is_not_changed_when_student_region_changes(): void
    {
        $student = User::factory()->student()->create();
        $gaza = $this->regionId('gaza');
        $west = $this->regionId('west_bank');
        $student->studentProfile()->create(['region_id' => $gaza]);
        $course = Course::factory()->create();
        $plan = AccessPlan::factory()->create(['course_id' => $course->id]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
            'region_id' => $gaza,
            'amount_paid' => 100,
            'currency' => 'ILS',
            'status' => 'active',
        ]);

        $student->studentProfile->update(['region_id' => $west]);
        $enrollment->refresh();

        $this->assertSame($gaza, $enrollment->region_id);
        $this->assertSame('100.00', (string) $enrollment->amount_paid);
        $this->assertSame($west, $student->fresh()->studentProfile->region_id);
    }

    public function test_teacher_cannot_manage_foreign_access_plan_through_own_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $own = Course::factory()->create(['teacher_id' => $teacher->id]);
        $foreign = Course::factory()->create();
        $foreignPlan = AccessPlan::factory()->create(['course_id' => $foreign->id, 'title' => 'خطة أجنبية']);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.packages.update', [$own, $foreignPlan]), [
                'title' => 'مسروقة',
                'status' => ContentStatus::Live->value,
                'semester_ids' => [$own->defaultSemester()->id],
                'prices' => [['region_id' => $this->regionId(), 'price' => 1]],
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('access_plans', [
            'id' => $foreignPlan->id,
            'title' => 'خطة أجنبية',
            'course_id' => $foreign->id,
        ]);
    }

    public function test_teacher_earnings_and_enrollments_are_scoped_to_own_courses(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $ownCourse = Course::factory()->create(['teacher_id' => $teacher->id, 'title' => 'مادتي']);
        $otherCourse = Course::factory()->create(['teacher_id' => $other->id, 'title' => 'مادة غيره']);
        $ownStudent = User::factory()->student()->create(['name' => 'طالب المعلم']);
        $otherStudent = User::factory()->student()->create(['name' => 'طالب الآخر']);

        Enrollment::factory()->create([
            'student_id' => $ownStudent->id,
            'course_id' => $ownCourse->id,
            'amount_paid' => 50,
        ]);
        Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'course_id' => $otherCourse->id,
            'amount_paid' => 999,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.earnings.index'))
            ->assertOk()
            ->assertSee('طالب المعلم')
            ->assertDontSee('طالب الآخر')
            ->assertDontSee('999.00');

        $this->actingAs($teacher)
            ->get(route('teacher.enrollments.index'))
            ->assertOk()
            ->assertSee('طالب المعلم')
            ->assertDontSee('طالب الآخر');

        $this->actingAs($teacher)
            ->get(route('teacher.courses.analytics', $otherCourse))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.overview', $otherCourse))
            ->assertForbidden();
    }

    public function test_course_with_enrollments_cannot_be_deleted(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        Enrollment::factory()->create(['course_id' => $course->id]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.destroy', $course))
            ->assertStatus(422);

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }
}
