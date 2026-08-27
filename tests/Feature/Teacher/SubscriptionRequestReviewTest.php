<?php

namespace Tests\Feature\Teacher;

use App\Enums\ContentStatus;
use App\Enums\SubscriptionRequestStatus;
use App\Models\AccessPlan;
use App\Models\AccessPlanPrice;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Notifications\SubscriptionApprovedNotification;
use App\Notifications\SubscriptionRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionRequestReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_request_is_approved_into_a_snapshot_enrollment(): void
    {
        Storage::fake('public');
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $plan = AccessPlan::factory()->create([
            'course_id' => $course->id,
            'title' => 'الفصل الأول',
            'status' => ContentStatus::Live,
            'access_duration_days' => 30,
        ]);
        $plan->semesters()->attach($course->defaultSemester()->id);
        AccessPlanPrice::factory()->create([
            'access_plan_id' => $plan->id,
            'region_id' => $this->regionId('gaza'),
            'price' => 100,
            'currency' => 'ILS',
        ]);

        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId('gaza')]);

        $this->actingAs($student)
            ->post(route('courses.subscribe.store', $course), [
                'access_plan_id' => $plan->id,
                'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('subscription_requests', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
            'package_id' => null,
            'status' => SubscriptionRequestStatus::Pending->value,
        ]);
        $this->assertSame(0, SubscriptionPackage::query()->count());

        $subscriptionRequest = SubscriptionRequest::query()->first();

        $this->actingAs($teacher)
            ->get(route('teacher.subscription-requests.index'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($course->title);

        $this->actingAs($teacher)
            ->post(route('teacher.subscription-requests.approve', $subscriptionRequest))
            ->assertRedirect(route('teacher.subscription-requests.index'));

        $subscriptionRequest->refresh();
        $this->assertSame(SubscriptionRequestStatus::Approved, $subscriptionRequest->status);
        $this->assertNotNull($subscriptionRequest->reviewed_at);

        $enrollment = Enrollment::query()->first();
        $this->assertNotNull($enrollment);
        $this->assertSame($student->id, $enrollment->student_id);
        $this->assertSame($course->id, $enrollment->course_id);
        $this->assertSame($plan->id, $enrollment->access_plan_id);
        $this->assertSame($this->regionId('gaza'), $enrollment->region_id);
        $this->assertSame('100.00', (string) $enrollment->amount_paid);
        $this->assertSame('ILS', $enrollment->currency);
        $this->assertNotNull($enrollment->starts_at);
        $this->assertTrue($enrollment->expires_at->isSameDay(now()->addDays(30)));
        $this->assertSame('active', $enrollment->status);
        $this->assertSame($teacher->id, $enrollment->granted_by);

        Notification::assertSentTo($student, SubscriptionApprovedNotification::class);
    }

    public function test_teacher_only_sees_requests_for_own_courses(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $ownCourse = Course::factory()->create(['teacher_id' => $teacher->id]);
        $otherCourse = Course::factory()->create(['teacher_id' => $otherTeacher->id]);
        $ownPlan = AccessPlan::factory()->create(['course_id' => $ownCourse->id]);
        $otherPlan = AccessPlan::factory()->create(['course_id' => $otherCourse->id]);
        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId()]);

        $own = SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $ownCourse->id,
            'access_plan_id' => $ownPlan->id,
        ]);
        SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $otherCourse->id,
            'access_plan_id' => $otherPlan->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.subscription-requests.index'))
            ->assertOk()
            ->assertSee($own->student->name)
            ->assertSee($ownCourse->title)
            ->assertDontSee($otherCourse->title);
    }

    public function test_reject_updates_status_with_optional_reason(): void
    {
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $plan = AccessPlan::factory()->create(['course_id' => $course->id]);
        $student = User::factory()->student()->create();
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.subscription-requests.reject', $subscriptionRequest), [
                'rejection_reason' => 'not-clear',
            ])
            ->assertRedirect();

        $subscriptionRequest->refresh();
        $this->assertSame(SubscriptionRequestStatus::Rejected, $subscriptionRequest->status);
        $this->assertSame('not-clear', $subscriptionRequest->rejection_reason);
        $this->assertDatabaseCount('enrollments', 0);
        Notification::assertSentTo($student, SubscriptionRejectedNotification::class);
    }

    public function test_teacher_cannot_approve_another_teachers_request(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create();
        $plan = AccessPlan::factory()->create(['course_id' => $course->id]);
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.subscription-requests.approve', $subscriptionRequest))
            ->assertForbidden();
    }
}
