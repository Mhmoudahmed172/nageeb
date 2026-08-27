<?php

namespace Tests\Feature\Teacher;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Region;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_shows_lesson_path_content_blocks_and_settings(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        Region::factory()->create(['name' => 'غزة']);
        LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Video,
            'title' => 'مقدمة الوحدة',
            'position' => 1,
            'data' => ['path' => 'lessons/videos/secret.mp4', 'state' => 'ready', 'duration' => 125],
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.lessons.edit', [$course, $lesson]))
            ->assertOk()
            ->assertSee('محتوى الدرس')
            ->assertSee('+ إضافة محتوى', false)
            ->assertSee('إتاحة المحتوى')
            ->assertSee('جميع المناطق')
            ->assertSee('مناطق محددة')
            ->assertSee('غزة')
            ->assertSee('مقدمة الوحدة')
            ->assertSee('حصة مباشرة')
            ->assertSee('إعدادات الدرس')
            ->assertSee('المدة التقديرية (دقيقة)', false)
            ->assertDontSee('secret.mp4');
    }

    public function test_create_page_collects_lesson_settings_before_the_builder(): void
    {
        [$teacher, $course] = $this->lesson();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.lessons.create', $course))
            ->assertOk()
            ->assertSee('درس جديد')
            ->assertSee('عنوان الدرس')
            ->assertSee('إنشاء ومتابعة إلى المحتوى');
    }

    public function test_teacher_can_add_a_text_block_and_upload_a_video_block(): void
    {
        Storage::fake('public');
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Text->value,
            ])
            ->assertRedirect();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Video->value,
                'file' => UploadedFile::fake()->create('intro.mp4', 120, 'video/mp4'),
            ])
            ->assertRedirect();

        $blocks = $lesson->contents()->get();
        $this->assertSame(
            [LessonContentType::Text, LessonContentType::Video],
            $blocks->pluck('type')->all(),
        );
        $this->assertSame([1, 2], $blocks->pluck('position')->all());
    }

    public function test_non_media_blocks_reject_file_uploads(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Text->value,
                'file' => UploadedFile::fake()->create('intro.mp4', 10, 'video/mp4'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, $lesson->contents()->count());
    }

    public function test_block_update_saves_content_and_regional_availability(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        $gaza = Region::factory()->create(['name' => 'غزة']);
        Region::factory()->create(['name' => 'الضفة الغربية']);
        $block = LessonContent::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => LessonContentType::Text,
            'position' => 1,
            'status' => ContentStatus::Draft,
        ]);

        $this->actingAs($teacher)
            ->putJson(route('teacher.courses.lesson-contents.update', [$course, $lesson, $block]), [
                'title' => 'شرح القاعدة',
                'status' => ContentStatus::Live->value,
                'region_scope' => 'selected',
                'region_ids' => [$gaza->id],
                'body' => 'نص الشرح',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $block->refresh();
        $this->assertSame('شرح القاعدة', $block->title);
        $this->assertSame(ContentStatus::Live, $block->status);
        $this->assertSame('نص الشرح', $block->data['body']);
        $this->assertSame([$gaza->id], $block->regions->pluck('id')->all());

        $this->actingAs($teacher)
            ->putJson(route('teacher.courses.lesson-contents.update', [$course, $lesson, $block]), [
                'status' => ContentStatus::Live->value,
                'region_scope' => 'all',
                'region_ids' => [$gaza->id],
            ])
            ->assertOk();

        $this->assertTrue($block->fresh()->isAvailableToAllRegions());
    }

    public function test_regional_availability_is_kept_per_block(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        $gaza = Region::factory()->create(['name' => 'غزة']);
        $restricted = LessonContent::factory()->create(['lesson_id' => $lesson->id, 'position' => 1]);
        $open = LessonContent::factory()->create(['lesson_id' => $lesson->id, 'position' => 2]);
        $restricted->regions()->sync([$gaza->id]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.lessons.update', [$course, $lesson]), [
                'title' => $lesson->title,
                'unit_id' => $lesson->unit_id,
                'status' => ContentStatus::Live->value,
                'is_preview' => '0',
                'save_action' => 'save',
            ])
            ->assertRedirect();

        $this->assertSame([$gaza->id], $restricted->fresh()->regions->pluck('id')->all());
        $this->assertTrue($open->fresh()->isAvailableToAllRegions());
    }

    public function test_publish_action_puts_the_lesson_live(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        $lesson->update(['status' => ContentStatus::Draft]);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.lessons.update', [$course, $lesson]), [
                'title' => $lesson->title,
                'unit_id' => $lesson->unit_id,
                'status' => ContentStatus::Draft->value,
                'is_preview' => '0',
                'estimated_duration' => 25,
                'save_action' => 'publish',
            ])
            ->assertRedirect();

        $lesson->refresh();
        $this->assertSame(ContentStatus::Live, $lesson->status);
        $this->assertSame(25, $lesson->estimated_duration);
    }

    public function test_teacher_can_delete_a_block(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        $block = LessonContent::factory()->create(['lesson_id' => $lesson->id, 'position' => 1]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.lesson-contents.destroy', [$course, $lesson, $block]))
            ->assertRedirect();

        $this->assertDatabaseMissing('lesson_contents', ['id' => $block->id]);
    }

    public function test_teacher_cannot_touch_blocks_of_another_teacher(): void
    {
        $intruder = User::factory()->teacher()->create();
        [, $course, $lesson] = $this->lesson();
        $block = LessonContent::factory()->create(['lesson_id' => $lesson->id, 'position' => 1]);

        $this->actingAs($intruder)
            ->get(route('teacher.courses.lessons.edit', [$course, $lesson]))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Text->value,
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->putJson(route('teacher.courses.lesson-contents.update', [$course, $lesson, $block]), [
                'status' => ContentStatus::Live->value,
                'region_scope' => 'all',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('teacher.courses.lesson-contents.destroy', [$course, $lesson, $block]))
            ->assertForbidden();
    }

    public function test_blocks_cannot_be_reached_through_another_lesson(): void
    {
        [$teacher, $course, $lesson] = $this->lesson();
        $otherLesson = Lesson::factory()->create(['unit_id' => $lesson->unit_id, 'position' => 2]);
        $block = LessonContent::factory()->create(['lesson_id' => $lesson->id, 'position' => 1]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.lesson-contents.destroy', [$course, $otherLesson, $block]))
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: Course, 2: Lesson}
     */
    private function lesson(): array
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create(['position' => 1]);
        $lesson = Lesson::factory()->create([
            'unit_id' => $unit->id,
            'title' => 'الدرس الأول',
            'position' => 1,
        ]);

        return [$teacher, $course, $lesson];
    }
}
