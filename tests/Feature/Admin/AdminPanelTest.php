<?php

namespace Tests\Feature\Admin;

use App\Enums\PayoutRequestStatus;
use App\Enums\StudentRegion;
use App\Enums\SubscriptionRequestStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PayoutRequest;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_shows_platform_totals(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region' => StudentRegion::Gaza]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $package = SubscriptionPackage::factory()->create([
            'course_id' => $course->id,
            'price_gaza' => 40,
        ]);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'granted_by' => $teacher->id,
        ]);
        SubscriptionRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'package_id' => $package->id,
            'status' => SubscriptionRequestStatus::Approved,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('إجمالي المعلمين')
            ->assertSee('40.00');
    }

    public function test_admin_can_verify_a_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $teacher->teacherProfile()->create([
            'specialization' => 'كيمياء',
            'is_verified' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.teachers.index'))
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee('غير موثّق');

        $this->actingAs($admin)
            ->post(route('admin.teachers.verify', $teacher))
            ->assertRedirect(route('admin.teachers.index'));

        $this->assertTrue($teacher->fresh()->teacherProfile->is_verified);
    }

    public function test_admin_can_settle_pending_payouts_only(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = PayoutRequest::factory()->create([
            'status' => PayoutRequestStatus::Pending,
            'amount' => 120,
        ]);
        PayoutRequest::factory()->create([
            'status' => PayoutRequestStatus::Settled,
            'amount' => 999,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payouts.index'))
            ->assertOk()
            ->assertSee('120.00')
            ->assertDontSee('999.00');

        $this->actingAs($admin)
            ->post(route('admin.payouts.settle', $pending))
            ->assertRedirect(route('admin.payouts.index'));

        $this->assertSame(PayoutRequestStatus::Settled, $pending->fresh()->status);
    }

    public function test_non_admin_cannot_access_admin_teachers(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('admin.teachers.index'))
            ->assertForbidden();
    }
}
