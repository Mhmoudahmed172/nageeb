<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Support\ExamAccess;
use App\Support\ExamAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamAccess $examAccess,
        private readonly ExamAttemptService $attempts,
    ) {}

    public function index(): View
    {
        $student = auth()->user();

        $exams = Exam::query()
            ->published()
            ->with(['course', 'lesson', 'unit', 'regions'])
            ->withCount('questions')
            ->get()
            ->filter(fn (Exam $exam) => $this->examAccess->studentCanAccessExam($student, $exam))
            ->values();

        return view('student.exams.index', [
            'exams' => $exams,
            'attempts' => ExamAttempt::query()
                ->where('student_id', $student->id)
                ->whereIn('exam_id', $exams->pluck('id'))
                ->get()
                ->groupBy('exam_id'),
        ]);
    }

    /**
     * Instructions screen: the only entry point into an attempt.
     */
    public function show(Exam $exam): View
    {
        $student = $this->authorizeExam($exam);

        $exam->loadCount('questions');

        return view('student.exams.show', [
            'exam' => $exam,
            'attemptsUsed' => $exam->attemptsUsedBy($student),
            'openAttempt' => $exam->openAttemptFor($student),
            'previousAttempts' => $exam->attempts()
                ->where('student_id', $student->id)
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->get(),
            'totalPoints' => $exam->totalPoints(),
        ]);
    }

    public function start(Exam $exam): RedirectResponse
    {
        $student = $this->authorizeExam($exam);

        abort_if($exam->isFileExam(), 422);
        abort_if($exam->questions()->doesntExist(), 422);

        if (! $exam->openAttemptFor($student) && ! $exam->studentHasAttemptsLeft($student)) {
            return redirect()
                ->route('student.exams.show', $exam)
                ->with('error', 'استنفدت عدد المحاولات المسموح بها لهذا الاختبار.');
        }

        $attempt = $this->attempts->startOrResume($student, $exam);

        if (! $attempt->isInProgress()) {
            return redirect()->route('student.exams.result', [$exam, $attempt]);
        }

        return redirect()->route('student.exams.take', [$exam, $attempt]);
    }

    public function take(Request $request, Exam $exam, ExamAttempt $attempt): View|RedirectResponse
    {
        $this->authorizeExam($exam);
        $attempt = $this->authorizeAttempt($exam, $attempt);

        if (! $attempt->isInProgress()) {
            return redirect()->route('student.exams.result', [$exam, $attempt]);
        }

        $questions = $this->orderedQuestions($exam, $attempt);
        $index = max(0, min((int) $request->query('q', 0), $questions->count() - 1));
        $current = $questions[$index] ?? null;

        abort_if($current === null, 404);

        $answers = $attempt->answers()->get()->keyBy('question_id');

        return view('student.exams.take', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
            'question' => $current,
            'options' => $this->orderedOptions($exam, $attempt, $current),
            'index' => $index,
            'answers' => $answers,
            'selected' => $answers->get($current->id)?->selectedIds() ?? [],
        ]);
    }

    public function answer(Request $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        $this->authorizeExam($exam);
        $attempt = $this->authorizeAttempt($exam, $attempt);

        if (! $attempt->isInProgress()) {
            return redirect()->route('student.exams.result', [$exam, $attempt]);
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer'],
            'goto' => ['nullable', 'integer', 'min:0'],
        ]);

        $question = $exam->questions()->with('options')->whereKey($data['question_id'])->first();
        abort_if($question === null, 404);

        $this->attempts->saveAnswer($attempt, $question, $data['option_ids'] ?? []);

        return redirect()->route('student.exams.take', [
            'exam' => $exam,
            'attempt' => $attempt,
            'q' => $data['goto'] ?? 0,
        ]);
    }

    public function submit(Request $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        $this->authorizeExam($exam);
        $attempt = $this->authorizeAttempt($exam, $attempt);

        if ($attempt->isInProgress()) {
            $data = $request->validate([
                'question_id' => ['nullable', 'integer'],
                'option_ids' => ['nullable', 'array'],
                'option_ids.*' => ['integer'],
            ]);

            if (! empty($data['question_id'])) {
                $question = $exam->questions()->with('options')->whereKey($data['question_id'])->first();

                if ($question) {
                    $this->attempts->saveAnswer($attempt, $question, $data['option_ids'] ?? []);
                }
            }

            $this->attempts->submit($attempt);
        }

        return redirect()->route('student.exams.result', [$exam, $attempt]);
    }

    public function result(Exam $exam, ExamAttempt $attempt): View|RedirectResponse
    {
        $this->authorizeExam($exam);
        $attempt = $this->authorizeAttempt($exam, $attempt);

        if ($attempt->isInProgress()) {
            return redirect()->route('student.exams.take', [$exam, $attempt]);
        }

        $attempt->load(['answers.question.options']);

        return view('student.exams.result', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $exam->questions()->with('options')->get(),
        ]);
    }

    private function authorizeExam(Exam $exam): \App\Models\User
    {
        $student = auth()->user();
        abort_unless($this->examAccess->studentCanAccessExam($student, $exam), 403);

        return $student;
    }

    private function authorizeAttempt(Exam $exam, ExamAttempt $attempt): ExamAttempt
    {
        abort_unless($attempt->exam_id === $exam->id, 404);
        abort_unless($attempt->student_id === auth()->id(), 404);

        return $this->attempts->closeIfTimedOut($attempt);
    }

    /**
     * Shuffling is seeded with the attempt id so the order survives a refresh.
     *
     * @return Collection<int, Question>
     */
    private function orderedQuestions(Exam $exam, ExamAttempt $attempt): Collection
    {
        $questions = $exam->questions()->with('options')->get();

        return $exam->shuffle_questions
            ? $questions->shuffle($attempt->id)->values()
            : $questions->values();
    }

    /**
     * @return Collection<int, \App\Models\QuestionOption>
     */
    private function orderedOptions(Exam $exam, ExamAttempt $attempt, Question $question): Collection
    {
        return $exam->shuffle_options
            ? $question->options->shuffle($attempt->id + $question->id)->values()
            : $question->options->values();
    }
}
