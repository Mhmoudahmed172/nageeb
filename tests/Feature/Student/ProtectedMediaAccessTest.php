<?php

namespace Tests\Feature\Student;

use App\Enums\ContentStatus;
use App\Enums\ExamDeliveryMode;
use App\Enums\LessonContentType;
use App\Models\AccessPlan;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProtectedMediaAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_enrolled_student_can_stream_a_live_video_without_a_public_url(): void
    {
        [$teacher, $course, $lesson, $student] = $this->enrolledLesson();
        $content = $this->storeVideo($teacher, $course, $lesson);

        $this->actingAs($student)
            ->get(route('media.lesson-contents.show', $content))
            ->assertOk();

        $public = $this->get('/storage/'.$content->data['path']);
        $this->assertFalse($public->isSuccessful());
        $this->assertFalse(Storage::disk('public')->exists($content->data['path']));
    }

    public function test_student_without_enrollment_cannot_access_the_file(): void
    {
        [$teacher, $course, $lesson] = $this->enrolledLesson(enroll: false);
        $student = $this->student();
        $content = $this->storeVideo($teacher, $course, $lesson);

        $this->actingAs($student)
            ->get(route('media.lesson-contents.show', $content))
            ->assertForbidden();
    }

    public function test_region_restriction_blocks_the_wrong_region(): void
    {
        [$teacher, $course, $lesson, $gazaStudent] = $this->enrolledLesson(region: 'gaza');
        $westStudent = $this->student('west_bank');
        Enrollment::factory()->create([
            'student_id' => $westStudent->id,
            'course_id' => $course->id,
        ]);

        $content = $this->storeVideo($teacher, $course, $lesson);
        $content->regions()->sync([$this->regionId('gaza')]);

        $this->actingAs($gazaStudent)->get(route('media.lesson-contents.show', $content))->assertOk();
        $this->actingAs($westStudent)->get(route('media.lesson-contents.show', $content))->assertForbidden();
    }

    public function test_access_plan_scope_is_respected_for_files(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $first = $course->defaultSemester();
        $second = $course->semesters()->create([
            'title' => 'الفصل الثاني',
            'position' => $course->nextSemesterPosition(),
        ]);
        $unit = Unit::factory()->create(['semester_id' => $second->id]);
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);

        $plan = AccessPlan::factory()->create(['course_id' => $course->id]);
        $plan->semesters()->sync([$first->id]);

        $student = $this->student();
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
        ]);

        $content = $this->storeVideo($teacher, $course, $lesson);

        $this->actingAs($student)
            ->get(route('media.lesson-contents.show', $content))
            ->assertForbidden();
    }

    public function test_expired_enrollment_blocks_media(): void
    {
        [$teacher, $course, $lesson, $student] = $this->enrolledLesson();
        Enrollment::query()->where('student_id', $student->id)->update([
            'expires_at' => now()->subDay(),
        ]);
        $content = $this->storeVideo($teacher, $course, $lesson);

        $this->actingAs($student)
            ->get(route('media.lesson-contents.show', $content))
            ->assertForbidden();
    }

    public function test_teacher_cannot_download_another_teachers_video(): void
    {
        [$teacher, $course, $lesson] = $this->enrolledLesson(enroll: false);
        $content = $this->storeVideo($teacher, $course, $lesson);
        $intruder = User::factory()->teacher()->create();

        $this->actingAs($intruder)
            ->get(route('media.lesson-contents.show', $content))
            ->assertForbidden();
    }

    public function test_teacher_can_upload_an_exam_paper_and_student_can_download_it_when_allowed(): void
    {
        [$teacher, $course, $lesson, $student] = $this->enrolledLesson();

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'title' => 'ورقة امتحان',
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'max_attempts' => 1,
                'passing_score' => 50,
                'status' => 'published',
                'delivery_mode' => ExamDeliveryMode::File->value,
                'region_scope' => 'all',
                'file' => UploadedFile::fake()->create('midterm.pdf', 80, 'application/pdf'),
            ])
            ->assertRedirect();

        $exam = Exam::query()->where('title', 'ورقة امتحان')->firstOrFail();
        $this->assertTrue($exam->isFileExam());
        $this->assertTrue($exam->hasPaperFile());
        Storage::disk('local')->assertExists($exam->file_path);
        Storage::disk('public')->assertMissing($exam->file_path);
        $this->assertStringNotContainsString('/storage/', (string) $exam->paperUrl());

        $this->actingAs($student)
            ->get(route('student.exams.show', $exam))
            ->assertOk()
            ->assertSee('فتح ورقة الاختبار', false);

        $this->actingAs($student)
            ->get(route('media.exams.show', $exam))
            ->assertOk();

        $this->actingAs($student)
            ->post(route('student.exams.start', $exam))
            ->assertStatus(422);
    }

    public function test_teacher_cannot_download_another_teachers_exam_paper(): void
    {
        [$teacher, $course, $lesson] = $this->enrolledLesson(enroll: false);

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'title' => 'ورقة سرية',
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'max_attempts' => 1,
                'passing_score' => 50,
                'status' => 'published',
                'delivery_mode' => ExamDeliveryMode::File->value,
                'region_scope' => 'all',
                'file' => UploadedFile::fake()->create('secret.pdf', 40, 'application/pdf'),
            ])
            ->assertRedirect();

        $exam = Exam::query()->where('title', 'ورقة سرية')->firstOrFail();
        $intruder = User::factory()->teacher()->create();

        $this->actingAs($intruder)
            ->get(route('media.exams.show', $exam))
            ->assertForbidden();
    }

    public function test_unauthenticated_users_cannot_hit_media_routes(): void
    {
        $content = LessonContent::factory()->create([
            'type' => LessonContentType::File,
            'data' => ['path' => 'lessons/files/x.pdf', 'disk' => 'local'],
        ]);

        $this->get(route('media.lesson-contents.show', $content))->assertRedirect(route('login'));
    }

    /**
     * @return array{0: User, 1: Course, 2: Lesson, 3?: User}
     */
    private function enrolledLesson(bool $enroll = true, string $region = 'gaza'): array
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);

        if (! $enroll) {
            return [$teacher, $course, $lesson];
        }

        $student = $this->student($region);
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        return [$teacher, $course, $lesson, $student];
    }

    private function student(string $region = 'gaza'): User
    {
        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId($region)]);

        return $student;
    }

    private function storeVideo(User $teacher, Course $course, Lesson $lesson): LessonContent
    {
        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.lesson-contents.store', [$course, $lesson]), [
                'type' => LessonContentType::Video->value,
                'file' => UploadedFile::fake()->create('intro.mp4', 80, 'video/mp4'),
            ])
            ->assertCreated();

        $content = $lesson->contents()->firstOrFail();
        $content->update(['status' => ContentStatus::Live]);

        return $content->fresh();
    }
}
