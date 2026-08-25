<?php

namespace Tests\Feature\Student;

use App\Enums\StudentRegion;
use App\Enums\SubscriptionRequestStatus;
use App\Models\Course;
use App\Models\SubscriptionPackage;
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
        $package = SubscriptionPackage::factory()->create([
            'course_id' => $course->id,
            'name' => 'الفصل الأول',
            'price_gaza' => 50,
            'price_west_bank_abroad' => 80,
        ]);

        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region' => StudentRegion::Gaza]);

        $this->actingAs($student)
            ->get(route('courses.subscribe', $course))
            ->assertOk()
            ->assertSee('رياضيات')
            ->assertSee('أ. سامي')
            ->assertSee('الفصل الأول')
            ->assertSee('50.00');

        $this->actingAs($student)
            ->post(route('courses.subscribe.store', $course), [
                'package_id' => $package->id,
                'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            ])
            ->assertRedirect(route('courses.subscribe.confirmation', $course));

        $this->assertDatabaseHas('subscription_requests', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'package_id' => $package->id,
            'status' => SubscriptionRequestStatus::Pending->value,
        ]);
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
