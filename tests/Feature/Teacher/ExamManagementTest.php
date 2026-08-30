<?php

namespace Tests\Feature\Teacher;

use App\Enums\ExamStatus;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_exams_page_lists_only_the_teachers_own_exams(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        Exam::factory()->forCourse($course)->published()->create(['title' => 'اختبار الوحدة الأولى']);

        $otherCourse = Course::factory()->create();
        Exam::factory()->forCourse($otherCourse)->create(['title' => 'اختبار معلم آخر']);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.index'))
            ->assertOk()
            ->assertSee('اختبار الوحدة الأولى', false)
            ->assertDontSee('اختبار معلم آخر', false);
    }

    public function test_teacher_can_create_an_exam_placed_on_a_lesson(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'title' => 'اختبار الدرس الأول',
                'description' => 'اختبار قصير',
                'course_id' => $course->id,
                'semester_id' => $unit->semester_id,
                'unit_id' => $unit->id,
                'lesson_id' => $lesson->id,
                'duration_minutes' => 20,
                'max_attempts' => 2,
                'passing_score' => 60,
                'status' => ExamStatus::Published->value,
                'show_results_immediately' => '1',
                'show_correct_answers' => '1',
                'shuffle_questions' => '0',
                'shuffle_options' => '0',
                'delivery_mode' => 'interactive',
                'region_scope' => 'all',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exams', [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'semester_id' => $unit->semester_id,
            'unit_id' => $unit->id,
            'lesson_id' => $lesson->id,
            'title' => 'اختبار الدرس الأول',
            'max_attempts' => 2,
            'status' => ExamStatus::Published->value,
        ]);
    }

    public function test_exam_cannot_be_placed_on_another_teachers_content(): void
    {
        $teacher = User::factory()->teacher()->create();
        $ownCourse = Course::factory()->create(['teacher_id' => $teacher->id]);
        $foreignCourse = Course::factory()->create();
        $foreignUnit = Unit::factory()->forCourse($foreignCourse)->create();

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'title' => 'محاولة اختراق',
                'course_id' => $ownCourse->id,
                'unit_id' => $foreignUnit->id,
                'max_attempts' => 1,
                'passing_score' => 50,
                'status' => ExamStatus::Draft->value,
                'delivery_mode' => 'interactive',
                'region_scope' => 'all',
            ])
            ->assertSessionHasErrors('unit_id');

        $this->assertDatabaseMissing('exams', ['title' => 'محاولة اختراق']);
    }

    public function test_teacher_cannot_use_another_teachers_course_when_creating_an_exam(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreignCourse = Course::factory()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'title' => 'اختبار غير مصرح',
                'course_id' => $foreignCourse->id,
                'max_attempts' => 1,
                'passing_score' => 50,
                'status' => ExamStatus::Draft->value,
                'delivery_mode' => 'interactive',
                'region_scope' => 'all',
            ])
            ->assertSessionHasErrors('course_id');
    }

    public function test_teacher_can_update_own_exam(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->create();

        $this->actingAs($teacher)
            ->put(route('teacher.exams.update', $exam), [
                'title' => 'اختبار محدث',
                'course_id' => $course->id,
                'max_attempts' => 3,
                'passing_score' => 70,
                'status' => ExamStatus::Published->value,
                'delivery_mode' => 'interactive',
                'region_scope' => 'all',
            ])
            ->assertRedirect(route('teacher.exams.show', $exam));

        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'title' => 'اختبار محدث',
            'max_attempts' => 3,
            'status' => ExamStatus::Published->value,
        ]);
    }

    public function test_teacher_cannot_view_update_or_delete_another_teachers_exam(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreignExam = Exam::factory()->create();

        $this->actingAs($teacher)->get(route('teacher.exams.show', $foreignExam))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.exams.edit', $foreignExam))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.exams.questions.index', $foreignExam))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.exams.results.index', $foreignExam))->assertForbidden();

        $this->actingAs($teacher)
            ->put(route('teacher.exams.update', $foreignExam), [
                'title' => 'مخترق',
                'course_id' => $foreignExam->course_id,
                'max_attempts' => 1,
                'passing_score' => 50,
                'status' => ExamStatus::Draft->value,
                'delivery_mode' => 'interactive',
                'region_scope' => 'all',
            ])
            ->assertForbidden();

        $this->actingAs($teacher)->delete(route('teacher.exams.destroy', $foreignExam))->assertForbidden();

        $this->assertDatabaseHas('exams', ['id' => $foreignExam->id, 'title' => $foreignExam->title]);
    }

    public function test_exam_with_attempts_cannot_be_deleted(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->create();
        $exam->attempts()->create([
            'student_id' => User::factory()->student()->create()->id,
            'attempt_number' => 1,
            'started_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.exams.destroy', $exam))
            ->assertRedirect();

        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
    }

    public function test_question_bank_is_isolated_per_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();
        Question::factory()->withOptions()->create([
            'teacher_id' => $teacher->id,
            'text' => 'سؤالي الخاص',
        ]);
        $foreignQuestion = Question::factory()->withOptions()->create(['text' => 'سؤال معلم آخر']);

        $this->actingAs($teacher)
            ->get(route('teacher.questions.index'))
            ->assertOk()
            ->assertSee('سؤالي الخاص', false)
            ->assertDontSee('سؤال معلم آخر', false);

        $this->actingAs($teacher)->get(route('teacher.questions.edit', $foreignQuestion))->assertForbidden();
        $this->actingAs($teacher)->delete(route('teacher.questions.destroy', $foreignQuestion))->assertForbidden();

        $this->assertDatabaseHas('questions', ['id' => $foreignQuestion->id]);
    }

    public function test_teacher_can_create_a_question_with_options(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.questions.store'), [
                'type' => QuestionType::MultipleChoice->value,
                'text' => 'ما عاصمة فلسطين؟',
                'points' => 2,
                'difficulty' => QuestionDifficulty::Easy->value,
                'course_id' => $course->id,
                'options' => [
                    ['text' => 'القدس'],
                    ['text' => 'غزة'],
                ],
                'correct_options' => [0],
            ])
            ->assertRedirect(route('teacher.questions.index'));

        $question = Question::query()->where('text', 'ما عاصمة فلسطين؟')->firstOrFail();

        $this->assertSame($teacher->id, $question->teacher_id);
        $this->assertCount(2, $question->options);
        $this->assertSame(['القدس'], $question->options->where('is_correct', true)->pluck('text')->all());
    }

    public function test_multiple_choice_question_requires_exactly_one_correct_option(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.questions.store'), [
                'type' => QuestionType::MultipleChoice->value,
                'text' => 'سؤال خاطئ',
                'points' => 1,
                'difficulty' => QuestionDifficulty::Easy->value,
                'options' => [
                    ['text' => 'أ'],
                    ['text' => 'ب'],
                ],
                'correct_options' => [0, 1],
            ])
            ->assertSessionHasErrors('correct_options');
    }

    public function test_teacher_can_attach_and_detach_own_questions_on_own_exam(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->create();
        $question = Question::factory()->withOptions()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.exams.questions.store', $exam), ['question_id' => $question->id])
            ->assertRedirect();

        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $exam->id,
            'question_id' => $question->id,
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.exams.questions.destroy', [$exam, $question]))
            ->assertRedirect();

        $this->assertDatabaseMissing('exam_questions', [
            'exam_id' => $exam->id,
            'question_id' => $question->id,
        ]);
    }

    public function test_teacher_cannot_attach_another_teachers_question(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->create();
        $foreignQuestion = Question::factory()->withOptions()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.exams.questions.store', $exam), ['question_id' => $foreignQuestion->id])
            ->assertForbidden();

        $this->assertDatabaseMissing('exam_questions', [
            'exam_id' => $exam->id,
            'question_id' => $foreignQuestion->id,
        ]);
    }

    public function test_results_page_shows_only_attempts_of_the_owning_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->published()->create();
        $student = User::factory()->student()->create(['name' => 'ليان أحمد']);
        $attempt = $exam->attempts()->create([
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now(),
            'status' => \App\Enums\AttemptStatus::Submitted,
            'score' => 8,
            'total_points' => 10,
            'percentage' => 80,
            'passed' => true,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.results.index', $exam))
            ->assertOk()
            ->assertSee('ليان أحمد', false)
            ->assertSee('80');

        $this->actingAs($teacher)
            ->get(route('teacher.exams.results.show', [$exam, $attempt]))
            ->assertOk();

        $otherTeacher = User::factory()->teacher()->create();
        $this->actingAs($otherTeacher)
            ->get(route('teacher.exams.results.show', [$exam, $attempt]))
            ->assertForbidden();
    }

    public function test_attempt_from_another_exam_is_not_reachable(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->create();
        $otherExam = Exam::factory()->forCourse($course)->create();
        $attempt = $otherExam->attempts()->create([
            'student_id' => User::factory()->student()->create()->id,
            'attempt_number' => 1,
            'started_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.results.show', [$exam, $attempt]))
            ->assertNotFound();
    }

    public function test_every_exam_and_question_screen_renders(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create();
        Lesson::factory()->create(['unit_id' => $unit->id]);

        $exam = Exam::factory()->forCourse($course)->published()->create();
        $question = Question::factory()->withOptions()->create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'unit_id' => $unit->id,
        ]);
        $exam->questions()->attach($question->id, ['position' => 1]);

        $this->actingAs($teacher)->get(route('teacher.exams.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.exams.create'))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.exams.show', $exam))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.exams.edit', $exam))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.exams.questions.index', $exam))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.exams.results.index', $exam))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.questions.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.questions.create'))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.questions.edit', $question))->assertOk();
    }

    public function test_teacher_can_reorder_questions_inside_an_exam(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $exam = Exam::factory()->forCourse($course)->create();
        $first = Question::factory()->withOptions()->create(['teacher_id' => $teacher->id]);
        $second = Question::factory()->withOptions()->create(['teacher_id' => $teacher->id]);
        $exam->questions()->attach([
            $first->id => ['position' => 1],
            $second->id => ['position' => 2],
        ]);

        $this->actingAs($teacher)
            ->put(route('teacher.exams.questions.update', [$exam, $second]), ['direction' => 'up'])
            ->assertRedirect();

        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $exam->id,
            'question_id' => $second->id,
            'position' => 1,
        ]);

        $this->actingAs($teacher)
            ->put(route('teacher.exams.questions.update', [$exam, $first]), ['points' => 5])
            ->assertRedirect();

        $this->assertEquals(5.0, (float) $exam->fresh()->pointsFor($first));
    }

    public function test_sidebar_links_to_the_exam_and_question_pages(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.exams.index'))
            ->assertOk()
            ->assertSee(route('teacher.exams.index'), false)
            ->assertSee(route('teacher.questions.index'), false);
    }
}
