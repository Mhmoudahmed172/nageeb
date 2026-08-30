<?php

namespace Tests\Feature\Student;

use App\Enums\AttemptStatus;
use App\Models\AccessPlan;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_exam_is_listed_for_an_enrolled_student(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);

        $published = $this->examWithQuestions($course, ['title' => 'اختبار منشور'], published: true);
        $this->examWithQuestions($course, ['title' => 'اختبار مسودة']);

        $this->actingAs($student)
            ->get(route('student.exams.index'))
            ->assertOk()
            ->assertSee('اختبار منشور', false)
            ->assertDontSee('اختبار مسودة', false);

        $this->actingAs($student)
            ->get(route('student.exams.show', $published))
            ->assertOk()
            ->assertSee('بدء الاختبار', false);
    }

    public function test_draft_exam_is_not_reachable_by_a_student(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);

        $draft = $this->examWithQuestions($course);

        $this->actingAs($student)->get(route('student.exams.show', $draft))->assertForbidden();
        $this->actingAs($student)->post(route('student.exams.start', $draft))->assertForbidden();
    }

    public function test_student_without_enrollment_cannot_reach_the_exam(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $exam = $this->examWithQuestions($course, published: true);

        $this->actingAs($student)->get(route('student.exams.show', $exam))->assertForbidden();
        $this->actingAs($student)->post(route('student.exams.start', $exam))->assertForbidden();
    }

    public function test_access_plan_scope_is_respected_for_semester_placement(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();

        $first = $course->defaultSemester();
        $second = $course->semesters()->create([
            'title' => 'الفصل الثاني',
            'position' => $course->nextSemesterPosition(),
        ]);

        $plan = AccessPlan::factory()->create(['course_id' => $course->id]);
        $plan->semesters()->sync([$first->id]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'access_plan_id' => $plan->id,
        ]);

        $allowed = $this->examWithQuestions($course, ['semester_id' => $first->id], published: true);
        $blocked = $this->examWithQuestions($course, ['semester_id' => $second->id], published: true);

        $this->actingAs($student)->get(route('student.exams.show', $allowed))->assertOk();
        $this->actingAs($student)->get(route('student.exams.show', $blocked))->assertForbidden();
    }

    public function test_region_restriction_is_respected(): void
    {
        $gazaStudent = $this->student('gaza');
        $westStudent = $this->student('west_bank');
        $course = Course::factory()->create();
        $this->enroll($gazaStudent, $course);
        $this->enroll($westStudent, $course);

        $exam = $this->examWithQuestions($course, published: true);
        $exam->regions()->sync([$this->regionId('gaza')]);

        $this->actingAs($gazaStudent)->get(route('student.exams.show', $exam))->assertOk();
        $this->actingAs($westStudent)->get(route('student.exams.show', $exam))->assertForbidden();
    }

    public function test_lesson_placed_exam_follows_lesson_access(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);

        $exam = $this->examWithQuestions($course, [
            'semester_id' => $unit->semester_id,
            'unit_id' => $unit->id,
            'lesson_id' => $lesson->id,
        ], published: true);

        $this->actingAs($student)->get(route('student.exams.show', $exam))->assertForbidden();

        $this->enroll($student, $course);

        $this->actingAs($student)->get(route('student.exams.show', $exam))->assertOk();
    }

    public function test_attempt_is_resumed_after_a_refresh_instead_of_starting_over(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);
        $exam = $this->examWithQuestions($course, ['max_attempts' => 1], published: true);

        $this->actingAs($student)->post(route('student.exams.start', $exam))->assertRedirect();
        $attempt = ExamAttempt::query()->where('student_id', $student->id)->firstOrFail();

        $this->actingAs($student)->post(route('student.exams.start', $exam))->assertRedirect(
            route('student.exams.take', [$exam, $attempt]),
        );

        $this->assertSame(1, ExamAttempt::query()->where('student_id', $student->id)->count());
    }

    public function test_answers_are_saved_while_navigating_and_scored_on_submit(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);

        $exam = Exam::factory()->forCourse($course)->published()->create(['passing_score' => 50]);
        $first = Question::factory()->withOptions(['٣', '٤', '٥'], [1])->create([
            'teacher_id' => $course->teacher_id,
            'points' => 1,
        ]);
        $second = Question::factory()->trueFalse()->create([
            'teacher_id' => $course->teacher_id,
            'points' => 1,
        ]);
        $exam->questions()->attach([
            $first->id => ['position' => 1],
            $second->id => ['position' => 2],
        ]);

        $this->actingAs($student)->post(route('student.exams.start', $exam));
        $attempt = ExamAttempt::query()->where('student_id', $student->id)->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.exams.take', [$exam, $attempt]))
            ->assertOk()
            ->assertSee($first->text, false);

        $this->actingAs($student)->post(route('student.exams.answer', [$exam, $attempt]), [
            'question_id' => $first->id,
            'option_ids' => [$first->options->where('is_correct', true)->first()->id],
            'goto' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => $first->id,
            'is_correct' => true,
        ]);

        $this->actingAs($student)->post(route('student.exams.submit', [$exam, $attempt]), [
            'question_id' => $second->id,
            'option_ids' => [$second->options->where('is_correct', false)->first()->id],
        ])->assertRedirect(route('student.exams.result', [$exam, $attempt]));

        $attempt->refresh();

        $this->assertSame(AttemptStatus::Submitted, $attempt->status);
        $this->assertEquals(1.0, (float) $attempt->score);
        $this->assertEquals(2.0, (float) $attempt->total_points);
        $this->assertEquals(50.0, (float) $attempt->percentage);
        $this->assertTrue((bool) $attempt->passed);
        $this->assertNotNull($attempt->submitted_at);
    }

    public function test_multiple_response_question_requires_the_exact_correct_set(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);

        $exam = Exam::factory()->forCourse($course)->published()->create();
        $question = Question::factory()->multipleResponse()->create([
            'teacher_id' => $course->teacher_id,
            'points' => 2,
        ]);
        $exam->questions()->attach($question->id, ['position' => 1]);

        $this->actingAs($student)->post(route('student.exams.start', $exam));
        $attempt = ExamAttempt::query()->where('student_id', $student->id)->firstOrFail();

        $correctIds = $question->options->where('is_correct', true)->pluck('id')->all();

        $this->actingAs($student)->post(route('student.exams.answer', [$exam, $attempt]), [
            'question_id' => $question->id,
            'option_ids' => [$correctIds[0]],
        ]);

        $this->assertDatabaseHas('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'is_correct' => false,
        ]);

        $this->actingAs($student)->post(route('student.exams.answer', [$exam, $attempt]), [
            'question_id' => $question->id,
            'option_ids' => $correctIds,
        ]);

        $this->assertDatabaseHas('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'is_correct' => true,
        ]);
    }

    public function test_attempt_limit_is_enforced(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);
        $exam = $this->examWithQuestions($course, ['max_attempts' => 1], published: true);

        $this->actingAs($student)->post(route('student.exams.start', $exam));
        $attempt = ExamAttempt::query()->where('student_id', $student->id)->firstOrFail();
        $this->actingAs($student)->post(route('student.exams.submit', [$exam, $attempt]));

        $this->actingAs($student)
            ->post(route('student.exams.start', $exam))
            ->assertRedirect(route('student.exams.show', $exam))
            ->assertSessionHas('error');

        $this->assertSame(1, ExamAttempt::query()->where('student_id', $student->id)->count());
    }

    public function test_expired_attempt_is_submitted_automatically(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $this->enroll($student, $course);
        $exam = $this->examWithQuestions($course, ['duration_minutes' => 10], published: true);

        $attempt = $exam->attempts()->create([
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(30),
            'expires_at' => now()->subMinutes(20),
        ]);

        $this->actingAs($student)
            ->get(route('student.exams.take', [$exam, $attempt]))
            ->assertRedirect(route('student.exams.result', [$exam, $attempt]));

        $attempt->refresh();

        $this->assertSame(AttemptStatus::Expired, $attempt->status);
        $this->assertNotNull($attempt->submitted_at);
    }

    public function test_student_cannot_open_another_students_attempt(): void
    {
        $owner = $this->student();
        $intruder = $this->student();
        $course = Course::factory()->create();
        $this->enroll($owner, $course);
        $this->enroll($intruder, $course);
        $exam = $this->examWithQuestions($course, published: true);

        $this->actingAs($owner)->post(route('student.exams.start', $exam));
        $attempt = ExamAttempt::query()->where('student_id', $owner->id)->firstOrFail();

        $this->actingAs($intruder)
            ->get(route('student.exams.take', [$exam, $attempt]))
            ->assertNotFound();

        $this->actingAs($intruder)
            ->post(route('student.exams.answer', [$exam, $attempt]), ['question_id' => 1])
            ->assertNotFound();
    }

    public function test_lesson_page_lists_published_exams_of_that_lesson(): void
    {
        $student = $this->student();
        $course = Course::factory()->create();
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id, 'title' => 'الدرس الأول']);
        $this->enroll($student, $course);

        $this->examWithQuestions($course, [
            'semester_id' => $unit->semester_id,
            'unit_id' => $unit->id,
            'lesson_id' => $lesson->id,
            'title' => 'اختبار الدرس الأول',
        ], published: true);

        $this->actingAs($student)
            ->get(route('student.my-courses.show', $course))
            ->assertOk()
            ->assertSee('اختبار الدرس الأول', false);
    }

    private function student(string $regionCode = 'gaza'): User
    {
        $student = User::factory()->student()->create();
        $student->studentProfile()->create(['region_id' => $this->regionId($regionCode)]);

        return $student;
    }

    private function enroll(User $student, Course $course): Enrollment
    {
        return Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function examWithQuestions(Course $course, array $attributes = [], bool $published = false): Exam
    {
        $factory = Exam::factory()->forCourse($course);

        if ($published) {
            $factory = $factory->published();
        }

        $exam = $factory->create($attributes);

        $question = Question::factory()->withOptions()->create(['teacher_id' => $course->teacher_id]);
        $exam->questions()->attach($question->id, ['position' => 1]);

        return $exam;
    }
}
