<?php

namespace Tests\Feature\Student;

use App\Enums\ContentStatus;
use App\Enums\SubscriptionRequestStatus;
use App\Models\AccessPlan;
use App\Models\AccessPlanPrice;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_region_price_and_can_submit_a_request(): void
    {
        Storage::fake('public');

        $teacher = User::factory()->teacher()->create(['name' => 'أ. سامي']);
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'رياضيات',
        ]);
        $plan = AccessPlan::factory()->create([
            'course_id' => $course->id,
            'title' => 'الفصل الأول',
            'status' => ContentStatus::Live,
        ]);
        $plan->semesters()->attach($course->defaultSemester()->id);
        AccessPlanPrice::factory()->create([
            'access_plan_id' => $plan->id,
            'region_id' => $this->regionId('gaza'),
            'price' => 50,
        ]);
        AccessPlanPrice::factory()->create([
            'access_plan_id' => $plan->id,
            'region_id' => $this->regionId('west_bank'),
            'price' => 80,
        ]);

        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId('gaza')]);

        $this->actingAs($student)
            ->get(route('courses.subscribe', $course))
            ->assertOk()
            ->assertSee('رياضيات')
            ->assertSee('أ. سامي')
            ->assertSee('الفصل الأول')
            ->assertSee('50.00');

        $this->actingAs($student)
            ->post(route('courses.subscribe.store', $course), [
                'access_plan_id' => $plan->id,
                'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            ])
            ->assertRedirect(route('courses.subscribe.confirmation', $course));

        $this->assertDatabaseHas('subscription_requests', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
            'package_id' => null,
            'status' => SubscriptionRequestStatus::Pending->value,
        ]);
        $this->assertDatabaseCount('subscription_packages', 0);
    }

    public function test_guest_cannot_open_subscribe_page(): void
    {
        $course = Course::factory()->create();

        $this->get(route('courses.subscribe', $course))->assertRedirect('/login');
    }

    public function test_teacher_cannot_submit_student_subscription(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create();

        $this->actingAs($teacher)
            ->get(route('courses.subscribe', $course))
            ->assertForbidden();
    }
}
