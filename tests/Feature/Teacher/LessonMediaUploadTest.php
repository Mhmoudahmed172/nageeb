<?php

namespace Tests\Feature\Teacher;

use App\Enums\LessonContentType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_teacher_can_upload_a_video_to_their_lesson(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Video->value,
                'file' => UploadedFile::fake()->create('intro.mp4', 512, 'video/mp4'),
            ])
            ->assertCreated()
            ->assertJsonPath('ok', true);

        $content = $lesson->contents()->first();
        $this->assertNotNull($content);
        $this->assertSame(LessonContentType::Video, $content->type);
        $this->assertSame('local', $content->data['disk']);
        $this->assertNotSame('intro.mp4', basename($content->data['path']));
        $this->assertSame('intro.mp4', $content->data['original_name']);
        $this->assertSame('uploaded', $content->data['upload_status']);
        $this->assertSame('ready', $content->data['processing_status']);
        Storage::disk('local')->assertExists($content->data['path']);
        Storage::disk('public')->assertMissing($content->data['path']);
        $this->assertStringNotContainsString('/storage/', (string) $content->accessUrl());
    }

    public function test_json_upload_returns_the_real_validation_message_instead_of_a_generic_failure(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Video->value,
                'file' => UploadedFile::fake()->create('malware.php', 10, 'text/x-php'),
            ])
            ->assertStatus(422)
            ->assertJsonFragment(['file' => ['نوع الملف غير مسموح لأسباب أمنية.']]);
    }

    public function test_oversized_files_are_rejected_with_an_arabic_size_message(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $response = $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::File->value,
                'file' => UploadedFile::fake()->create('huge.pdf', 60000, 'application/pdf'),
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('حجم الملف أكبر من الحد المسموح', (string) $response->json('errors.file.0'));
    }

    public function test_teacher_can_upload_a_pdf_and_an_image(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::File->value,
                'file' => UploadedFile::fake()->create('notes.pdf', 200, 'application/pdf'),
            ])
            ->assertCreated();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::File->value,
                'file' => UploadedFile::fake()->image('slide.jpg'),
            ])
            ->assertCreated();

        $this->assertSame(2, $lesson->contents()->count());
        Storage::disk('local')->assertExists($lesson->contents()->first()->data['path']);
    }

    public function test_teacher_can_add_an_external_url_without_uploading_a_file(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Link->value,
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ])
            ->assertCreated();

        $block = $lesson->contents()->first();
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $block->data['url']);
        $this->assertSame('external_link', $block->data['source']);
        $this->assertArrayNotHasKey('path', $block->data);
    }

    public function test_invalid_external_urls_are_rejected(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        $block = LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Link,
            'data' => [],
        ]);

        $this->actingAs($teacher)
            ->putJson(route('teacher.courses.lesson-contents.update', [$course, $lesson, $block]), [
                'status' => 'draft',
                'region_scope' => 'all',
                'url' => 'javascript:alert(1)',
            ])
            ->assertStatus(422);
    }

    public function test_teacher_cannot_upload_to_another_teachers_lesson(): void
    {
        [, $course, $lesson] = $this->lesson();
        $intruder = User::factory()->teacher()->create();

        $this->actingAs($intruder)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::File->value,
                'file' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertSame(0, $lesson->contents()->count());
    }

    public function test_teacher_cannot_delete_another_teachers_file(): void
    {
        [, $course, $lesson] = $this->lesson();
        $block = LessonContent::factory()->create(['lesson_id' => $lesson->id]);
        $intruder = User::factory()->teacher()->create();

        $this->actingAs($intruder)
            ->delete(route('teacher.courses.lesson-contents.destroy', [$course, $lesson, $block]))
            ->assertForbidden();

        $this->assertDatabaseHas('lesson_contents', ['id' => $block->id]);
    }

    public function test_svg_and_php_uploads_are_rejected(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::File->value,
                'file' => UploadedFile::fake()->create('icon.svg', 8, 'image/svg+xml'),
            ])
            ->assertStatus(422);

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::File->value,
                'file' => UploadedFile::fake()->create('shell.php', 8, 'text/plain'),
            ])
            ->assertStatus(422);
    }

    /**
     * @return array{0: User, 1: Course, 2: Lesson}
     */
    private function lesson(): array
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);

        return [$teacher, $course, $lesson];
    }
}
