<?php

namespace Tests\Feature\Teacher;

use App\Enums\StudentRegion;
use App\Enums\SubscriptionRequestStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Notifications\SubscriptionApprovedNotification;
use App\Notifications\SubscriptionRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionRequestReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_only_sees_requests_for_own_courses(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $ownCourse = Course::factory()->create(['teacher_id' => $teacher->id]);
        $otherCourse = Course::factory()->create(['teacher_id' => $otherTeacher->id]);
        $ownPackage = SubscriptionPackage::factory()->create(['course_id' => $ownCourse->id]);
        $otherPackage = SubscriptionPackage::factory()->create(['course_id' => $otherCourse->id]);
        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region' => StudentRegion::Gaza]);

        $own = SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $ownCourse->id,
            'package_id' => $ownPackage->id,
        ]);
        $foreign = SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $otherCourse->id,
            'package_id' => $otherPackage->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.subscription-requests.index'))
            ->assertOk()
            ->assertSee($own->student->name)
            ->assertSee($ownCourse->title)
            ->assertDontSee($otherCourse->title);
    }

    public function test_approve_creates_enrollment_in_one_transaction(): void
    {
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $package = SubscriptionPackage::factory()->create(['course_id' => $course->id]);
        $student = User::factory()->student()->create();
        $request = SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'package_id' => $package->id,
            'status' => SubscriptionRequestStatus::Pending,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.subscription-requests.approve', $request))
            ->assertRedirect(route('teacher.subscription-requests.index'));

        $request->refresh();
        $this->assertSame(SubscriptionRequestStatus::Approved, $request->status);
        $this->assertNotNull($request->reviewed_at);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'granted_by' => $teacher->id,
        ]);
        $this->assertSame(1, Enrollment::query()->count());
        Notification::assertSentTo($student, SubscriptionApprovedNotification::class);
    }

    public function test_reject_updates_status_with_optional_reason(): void
    {
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $package = SubscriptionPackage::factory()->create(['course_id' => $course->id]);
        $student = User::factory()->student()->create();
        $request = SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.subscription-requests.reject', $request), [
                'rejection_reason' => 'الإيصال غير واضح',
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame(SubscriptionRequestStatus::Rejected, $request->status);
        $this->assertSame('الإيصال غير واضح', $request->rejection_reason);
        $this->assertDatabaseCount('enrollments', 0);
        Notification::assertSentTo($student, SubscriptionRejectedNotification::class);
    }

    public function test_teacher_cannot_approve_another_teachers_request(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create();
        $package = SubscriptionPackage::factory()->create(['course_id' => $course->id]);
        $request = SubscriptionRequest::factory()->create([
            'course_id' => $course->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.subscription-requests.approve', $request))
            ->assertForbidden();
    }
}
