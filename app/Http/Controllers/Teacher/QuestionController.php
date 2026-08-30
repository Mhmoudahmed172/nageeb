<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveQuestionRequest;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Question::class);

        $teacherId = auth()->id();
        $search = trim((string) $request->query('search', ''));
        $courseId = $request->query('course_id');
        $unitId = $request->query('unit_id');
        $type = $request->query('type');
        $difficulty = $request->query('difficulty');

        $questions = Question::query()
            ->forTeacher($teacherId)
            ->with(['course', 'unit'])
            ->withCount('exams')
            ->when($search !== '', fn ($query) => $query->where('text', 'like', '%'.$search.'%'))
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->when($unitId, fn ($query) => $query->where('unit_id', $unitId))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($difficulty, fn ($query) => $query->where('difficulty', $difficulty))
            ->latest('updated_at')
            ->get();

        return view('teacher.questions.index', [
            'questions' => $questions,
            'total' => Question::query()->forTeacher($teacherId)->count(),
            'courses' => $this->teacherCourses(),
            'search' => $search,
            'courseId' => $courseId,
            'unitId' => $unitId,
            'type' => $type,
            'difficulty' => $difficulty,
            'types' => QuestionType::cases(),
            'difficulties' => QuestionDifficulty::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Question::class);

        return view('teacher.questions.create', [
            'courses' => $this->teacherCourses(),
            'types' => QuestionType::cases(),
            'difficulties' => QuestionDifficulty::cases(),
        ]);
    }

    public function store(SaveQuestionRequest $request): RedirectResponse
    {
        $this->authorize('create', Question::class);

        DB::transaction(function () use ($request): void {
            $question = Question::query()->create([
                ...$request->questionAttributes(),
                'teacher_id' => auth()->id(),
            ]);

            $question->options()->createMany($request->optionRows());
        });

        return redirect()
            ->route('teacher.questions.index')
            ->with('status', 'تمت إضافة السؤال إلى بنك الأسئلة.');
    }

    public function edit(Question $question): View
    {
        $this->authorize('update', $question);

        $question->load('options');

        return view('teacher.questions.edit', [
            'question' => $question,
            'courses' => $this->teacherCourses(),
            'types' => QuestionType::cases(),
            'difficulties' => QuestionDifficulty::cases(),
        ]);
    }

    public function update(SaveQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        DB::transaction(function () use ($request, $question): void {
            $question->update($request->questionAttributes());
            $question->options()->delete();
            $question->options()->createMany($request->optionRows());
        });

        return redirect()
            ->route('teacher.questions.index')
            ->with('status', 'تم حفظ السؤال.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);

        if ($question->answers()->exists()) {
            return back()->with('error', 'لا يمكن حذف سؤال أجاب عنه طلاب. يمكنك إزالته من الاختبارات بدلًا من ذلك.');
        }

        $question->delete();

        return redirect()
            ->route('teacher.questions.index')
            ->with('status', 'تم حذف السؤال.');
    }

    private function teacherCourses()
    {
        return Course::query()
            ->forTeacher(auth()->id())
            ->with('semesters.units')
            ->orderBy('title')
            ->get();
    }
}
