<?php

namespace Tests\Feature\Teacher;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_request_payout_and_update_profile(): void
    {
        Storage::fake('public');
        $teacher = User::factory()->teacher()->create();
        $teacher->teacherProfile()->create([
            'specialization' => 'رياضيات',
            'bio' => 'نبذة قديمة',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.payouts.store'), [
                'amount' => 250,
                'bank_details' => 'بنك فلسطين — 111',
            ])
            ->assertRedirect(route('teacher.payouts.index'));

        $this->assertDatabaseHas('payout_requests', [
            'teacher_id' => $teacher->id,
            'amount' => 250,
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.profile.update'), [
                '_method' => 'PUT',
                'specialization' => 'فيزياء',
                'bio' => 'نبذة محدّثة',
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertRedirect(route('teacher.profile.edit'));

        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $teacher->id,
            'specialization' => 'فيزياء',
            'bio' => 'نبذة محدّثة',
        ]);
    }

    public function test_public_teacher_profile_shows_live_courses_only(): void
    {
        $teacher = User::factory()->teacher()->create(['name' => 'أ. ليلى']);
        $teacher->teacherProfile()->create(['specialization' => 'عربي', 'bio' => 'معلمة لغة']);
        Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'نحو منشور',
            'status' => 'live',
        ]);
        Course::factory()->create([
            'teacher_id' => $teacher->id,
            'title' => 'مسودة مخفية',
            'status' => 'draft',
        ]);

        $this->get(route('teachers.show', $teacher))
            ->assertOk()
            ->assertSee('أ. ليلى')
            ->assertSee('نحو منشور')
            ->assertDontSee('مسودة مخفية');
    }

    public function test_teacher_can_update_account_settings(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->put(route('teacher.settings.update'), [
                'name' => 'اسم جديد',
                'email' => 'new-teacher@example.com',
                'phone' => '0590000000',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'name' => 'اسم جديد',
            'email' => 'new-teacher@example.com',
        ]);
    }
}
