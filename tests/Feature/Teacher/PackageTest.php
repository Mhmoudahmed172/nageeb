<?php

namespace Tests\Feature\Teacher;

use App\Enums\ContentStatus;
use App\Models\AccessPlan;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_add_multiple_access_plans_to_the_same_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $semester = $course->defaultSemester();
        $gaza = $this->regionId('gaza');
        $west = $this->regionId('west_bank');

        $payload = function (string $title, int $gazaPrice, int $westPrice) use ($semester, $gaza, $west) {
            return [
                'title' => $title,
                'status' => ContentStatus::Live->value,
                'semester_ids' => [$semester->id],
                'prices' => [
                    ['region_id' => $gaza, 'price' => $gazaPrice],
                    ['region_id' => $west, 'price' => $westPrice],
                ],
            ];
        };

        $this->actingAs($teacher)
            ->post(route('teacher.courses.packages.store', $course), $payload('الفصل الأول', 40, 70))
            ->assertRedirect(route('teacher.courses.packages.index', $course));

        $this->actingAs($teacher)
            ->post(route('teacher.courses.packages.store', $course), $payload('باقة سنوية', 90, 140))
            ->assertRedirect();

        $this->assertDatabaseCount('access_plans', 2);
        $this->assertEquals(2, $course->accessPlans()->count());
    }

    public function test_access_plan_page_uses_course_semesters_and_active_regions(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $plan = AccessPlan::factory()->create(['course_id' => $course->id, 'title' => 'المادة كاملة']);
        $plan->semesters()->attach($course->defaultSemester());

        $this->actingAs($teacher)
            ->get(route('teacher.courses.packages.index', $course))
            ->assertOk()
            ->assertSee('خطط الوصول')
            ->assertSee('الفصول التي يحصل الطالب عليها')
            ->assertSee('السعر حسب منطقة الطالب')
            ->assertSee('المادة كاملة')
            ->assertSee('غزة')
            ->assertSee('الضفة الغربية');
    }

    public function test_teacher_cannot_manage_another_teachers_course_packages(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherCourse = Course::factory()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.packages.index', $otherCourse))
            ->assertForbidden();
    }

    public function test_teacher_can_update_and_delete_an_access_plan(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $plan = AccessPlan::factory()->create(['course_id' => $course->id, 'title' => 'قديم']);
        $plan->semesters()->attach($course->defaultSemester()->id);
        $gaza = $this->regionId('gaza');
        $west = $this->regionId('west_bank');

        $this->actingAs($teacher)
            ->put(route('teacher.courses.packages.update', [$course, $plan]), [
                'title' => 'الفصل الثاني',
                'status' => ContentStatus::Live->value,
                'semester_ids' => [$course->defaultSemester()->id],
                'prices' => [
                    ['region_id' => $gaza, 'price' => 45],
                    ['region_id' => $west, 'price' => 75],
                ],
            ])
            ->assertRedirect(route('teacher.courses.packages.index', $course));

        $this->assertDatabaseHas('access_plans', [
            'id' => $plan->id,
            'title' => 'الفصل الثاني',
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.packages.destroy', [$course, $plan]))
            ->assertRedirect();

        $this->assertDatabaseMissing('access_plans', ['id' => $plan->id]);
    }
}
