<?php

namespace Tests\Feature\Teacher;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrolledStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_only_own_course_enrollments_and_can_filter(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id, 'title' => 'فيزياء']);
        $otherCourse = Course::factory()->create(['title' => 'كيمياء']);

        $gazaStudent = User::factory()->student()->create(['name' => 'خالد غزة']);
        $gazaStudent->studentProfile()->create(['region_id' => $this->regionId('gaza')]);
        $westStudent = User::factory()->student()->create(['name' => 'سارة ضفة']);
        $westStudent->studentProfile()->create(['region_id' => $this->regionId('west_bank')]);
        $otherStudent = User::factory()->student()->create(['name' => 'طالب آخر']);
        $otherStudent->studentProfile()->create(['region_id' => $this->regionId('gaza')]);

        Enrollment::factory()->create([
            'student_id' => $gazaStudent->id,
            'course_id' => $course->id,
            'granted_by' => $teacher->id,
        ]);
        Enrollment::factory()->create([
            'student_id' => $westStudent->id,
            'course_id' => $course->id,
            'granted_by' => $teacher->id,
        ]);
        Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'course_id' => $otherCourse->id,
            'granted_by' => $otherCourse->teacher_id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.enrollments.index'))
            ->assertOk()
            ->assertSee('خالد غزة')
            ->assertSee('سارة ضفة')
            ->assertDontSee('طالب آخر');

        $this->actingAs($teacher)
            ->get(route('teacher.enrollments.index', [
                'search' => 'خالد',
                'region' => 'gaza',
                'course_id' => $course->id,
            ]))
            ->assertOk()
            ->assertSee('خالد غزة')
            ->assertDontSee('سارة ضفة');
    }
}
