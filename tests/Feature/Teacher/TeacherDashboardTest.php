<?php

namespace Tests\Feature\Teacher;

use App\Enums\CourseStatus;
use App\Enums\SubscriptionRequestStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Notifications\StudentInactivityReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_real_kpis_and_at_risk_students(): void
    {
        $teacher = User::factory()->teacher()->create();
        $teacher->teacherProfile()->create(['is_verified' => true, 'specialization' => 'رياضيات']);
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'status' => CourseStatus::Live,
            'title' => 'تفاضل وتكامل',
        ]);
        $package = SubscriptionPackage::factory()->create(['course_id' => $course->id]);
        $student = User::factory()->student()->create(['name' => 'ليان أحمد']);
        $student->studentProfile()->create(['region_id' => $this->regionId()]);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'granted_by' => $teacher->id,
            'expires_at' => null,
            'granted_at' => now()->subDays(20),
        ]);
        SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'package_id' => $package->id,
            'status' => SubscriptionRequestStatus::Pending,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('عدد الطلاب')
            ->assertSee('ليان أحمد')
            ->assertSee('تفاضل وتكامل')
            ->assertSee('يتطلب انتباهك')
            ->assertSee('+ إضافة مادة');
    }

    public function test_dashboard_empty_states_when_teacher_has_no_activity(): void
    {
        $teacher = User::factory()->teacher()->create();
        $teacher->teacherProfile()->create(['is_verified' => false]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('ابدأ ببناء أول مادة تعليمية لك.')
            ->assertSee('+ إضافة مادة');
    }

    public function test_teacher_can_send_inactivity_reminder(): void
    {
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $student = User::factory()->student()->create();
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'granted_by' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.dashboard.remind', $student), [
                'course_id' => $course->id,
            ])
            ->assertRedirect(route('teacher.dashboard'));

        Notification::assertSentTo($student, StudentInactivityReminderNotification::class);
    }
}
