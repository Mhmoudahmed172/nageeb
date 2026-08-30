<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Unit;
use App\Models\User;
use App\Support\MediaStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateMediaDeploymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_private_upload_is_not_on_the_public_disk_or_storage_symlink(): void
    {
        [$teacher, $course, $lesson, $student] = $this->enrolledLesson();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Video->value,
                'file' => UploadedFile::fake()->create('intro.mp4', 80, 'video/mp4'),
            ])
            ->assertCreated();

        $content = $lesson->contents()->firstOrFail();
        $content->update(['status' => ContentStatus::Live]);
        $path = (string) $content->data['path'];
        $disk = (string) $content->data['disk'];

        $this->assertSame(MediaStore::defaultDisk(), $disk);
        $this->assertTrue(Storage::disk($disk)->exists($path));
        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertStringNotContainsString('http', $path);

        $public = $this->get('/storage/'.$path);
        $this->assertFalse($public->isSuccessful());

        $this->actingAs($student)
            ->get(route('media.lesson-contents.show', $content))
            ->assertOk();

        $outsider = User::factory()->student()->create();
        $outsider->studentProfile()->create(['region_id' => $this->regionId()]);

        $this->actingAs($outsider)
            ->get(route('media.lesson-contents.show', $content))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Course, 2: Lesson, 3: User}
     */
    private function enrolledLesson(): array
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);
        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId()]);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        return [$teacher, $course, $lesson, $student];
    }
}
