<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\View\View;

class ExamResultController extends Controller
{
    public function index(Exam $exam): View
    {
        $this->authorize('viewResults', $exam);

        $attempts = $exam->attempts()
            ->with('student')
            ->latest('started_at')
            ->get();
        $graded = $attempts->whereNotNull('submitted_at');

        return view('teacher.exams.results', [
            'exam' => $exam->load('course'),
            'attempts' => $attempts,
            'stats' => [
                'attempts' => $attempts->count(),
                'students' => $attempts->pluck('student_id')->unique()->count(),
                'average' => $graded->isEmpty() ? null : round((float) $graded->avg('percentage'), 1),
                'passed' => $graded->where('passed', true)->count(),
            ],
        ]);
    }

    public function show(Exam $exam, ExamAttempt $attempt): View
    {
        $this->authorize('viewResults', $exam);
        abort_unless($attempt->exam_id === $exam->id, 404);

        $attempt->load(['student', 'answers.question.options']);

        return view('teacher.exams.attempt', [
            'exam' => $exam->load('questions.options'),
            'attempt' => $attempt,
        ]);
    }
}
