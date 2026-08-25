<?php

namespace Tests\Feature\Teacher;

use App\Models\Course;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_add_multiple_packages_to_the_same_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.packages.store', $course), [
                'name' => 'الفصل الأول',
                'price_gaza' => 40,
                'price_west_bank_abroad' => 70,
                'duration_label' => 'فصل دراسي',
            ])
            ->assertRedirect(route('teacher.courses.packages.index', $course));

        $this->actingAs($teacher)
            ->post(route('teacher.courses.packages.store', $course), [
                'name' => 'باقة سنوية',
                'price_gaza' => 90,
                'price_west_bank_abroad' => 140,
                'duration_label' => 'سنة',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('subscription_packages', 2);
        $this->assertEquals(2, $course->packages()->count());
    }

    public function test_teacher_cannot_manage_another_teachers_course_packages(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherCourse = Course::factory()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.packages.index', $otherCourse))
            ->assertForbidden();
    }

    public function test_teacher_can_update_and_delete_a_package(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $package = SubscriptionPackage::factory()->create(['course_id' => $course->id]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.packages.update', [$course, $package]), [
                'name' => 'الفصل الثاني',
                'price_gaza' => 45,
                'price_west_bank_abroad' => 75,
                'duration_label' => 'فصل دراسي',
            ])
            ->assertRedirect(route('teacher.courses.packages.index', $course));

        $this->assertDatabaseHas('subscription_packages', [
            'id' => $package->id,
            'name' => 'الفصل الثاني',
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.packages.destroy', [$course, $package]))
            ->assertRedirect();

        $this->assertDatabaseMissing('subscription_packages', ['id' => $package->id]);
    }
}
