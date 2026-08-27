<?php

namespace Tests\Feature\Teacher;

use App\Enums\ContentStatus;
use App\Models\Course;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_course_shows_unit_empty_state(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $course))
            ->assertOk()
            ->assertSee('أضف أول وحدة لهذا الفصل.', false)
            ->assertSee('+ إضافة وحدة', false);
    }

    public function test_teacher_can_create_a_unit(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.units.store', $course), [
                'title' => 'الوحدة الأولى',
                'description' => 'مقدمة',
                'status' => ContentStatus::Draft->value,
            ])
            ->assertRedirect(route('teacher.courses.content', $course));

        $this->assertDatabaseHas('units', [
            'semester_id' => $course->defaultSemester()->id,
            'title' => 'الوحدة الأولى',
            'position' => 1,
            'status' => ContentStatus::Draft->value,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.units.store', $course), [
                'title' => 'الوحدة الثانية',
                'status' => ContentStatus::Live->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('units', [
            'semester_id' => $course->defaultSemester()->id,
            'title' => 'الوحدة الثانية',
            'position' => 2,
        ]);
    }

    public function test_teacher_can_reorder_units_only_inside_owned_semester(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $semester = $course->defaultSemester();
        $first = Unit::factory()->create(['semester_id' => $semester->id, 'position' => 1]);
        $second = Unit::factory()->create(['semester_id' => $semester->id, 'position' => 2]);

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.units.reorder', [$course, $semester]), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertOk();

        $this->assertDatabaseHas('units', ['id' => $second->id, 'position' => 1]);
        $this->assertDatabaseHas('units', ['id' => $first->id, 'position' => 2]);

        $otherCourse = Course::factory()->create(['teacher_id' => $teacher->id]);
        $foreignUnit = Unit::factory()->create(['semester_id' => $otherCourse->defaultSemester()->id]);

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.units.reorder', [$course, $semester]), [
                'ids' => [$first->id, $foreignUnit->id],
            ])
            ->assertUnprocessable();
    }

    public function test_teacher_can_update_a_unit(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create([
            'title' => 'قديم',
            'position' => 1,
            'status' => ContentStatus::Draft,
        ]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.units.update', [$course, $unit]), [
                'title' => 'الوحدة الأولى',
                'description' => 'محدّث',
                'status' => ContentStatus::Live->value,
            ])
            ->assertRedirect(route('teacher.courses.content', $course));

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'title' => 'الوحدة الأولى',
            'description' => 'محدّث',
            'status' => ContentStatus::Live->value,
            'position' => 1,
        ]);
    }

    public function test_teacher_can_delete_a_unit_and_positions_are_resequenced(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $first = Unit::factory()->forCourse($course)->create(['title' => 'أ', 'position' => 1]);
        $second = Unit::factory()->forCourse($course)->create(['title' => 'ب', 'position' => 2]);
        $third = Unit::factory()->forCourse($course)->create(['title' => 'ج', 'position' => 3]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.units.destroy', [$course, $second]))
            ->assertRedirect(route('teacher.courses.content', $course));

        $this->assertDatabaseMissing('units', ['id' => $second->id]);
        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(2, $third->fresh()->position);
    }

    public function test_teacher_can_reorder_units(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $first = Unit::factory()->forCourse($course)->create(['title' => 'الأولى', 'position' => 1]);
        $second = Unit::factory()->forCourse($course)->create(['title' => 'الثانية', 'position' => 2]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.units.move', [$course, $second]), [
                'direction' => 'up',
            ])
            ->assertRedirect(route('teacher.courses.content', $course));

        $this->assertSame(1, $second->fresh()->position);
        $this->assertSame(2, $first->fresh()->position);
    }

    public function test_teacher_cannot_manage_units_on_another_teachers_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreignCourse = Course::factory()->create();
        $foreignUnit = Unit::factory()->forCourse($foreignCourse)->create([
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $foreignCourse))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.units.create', $foreignCourse))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.units.store', $foreignCourse), [
                'title' => 'وحدة دخيلة',
                'status' => ContentStatus::Draft->value,
            ])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->put(route('teacher.courses.units.update', [$foreignCourse, $foreignUnit]), [
                'title' => 'تعديل',
                'status' => ContentStatus::Live->value,
            ])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.units.destroy', [$foreignCourse, $foreignUnit]))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.units.move', [$foreignCourse, $foreignUnit]), [
                'direction' => 'up',
            ])
            ->assertForbidden();
    }

    public function test_scoped_binding_rejects_unit_from_another_course(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $otherCourse = Course::factory()->create(['teacher_id' => $teacher->id]);
        $foreignUnit = Unit::factory()->forCourse($otherCourse)->create([
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.units.edit', [$course, $foreignUnit]))
            ->assertNotFound();
    }
}
