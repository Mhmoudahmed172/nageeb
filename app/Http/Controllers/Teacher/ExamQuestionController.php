<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExamQuestionController extends Controller
{
    public function index(Request $request, Exam $exam): View|RedirectResponse
    {
        $this->authorize('update', $exam);

        if ($exam->isFileExam()) {
            return redirect()
                ->route('teacher.exams.show', $exam)
                ->with('error', 'هذا اختبار مرفق ولا يحتوي على بنك أسئلة تفاعلي.');
        }

        $exam->load(['questions.options', 'course']);
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');
        $difficulty = $request->query('difficulty');

        $bank = Question::query()
            ->forTeacher(auth()->id())
            ->with('options')
            ->withCount('options')
            ->whereNotIn('id', $exam->questions->pluck('id'))
            ->when($search !== '', fn ($query) => $query->where('text', 'like', '%'.$search.'%'))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($difficulty, fn ($query) => $query->where('difficulty', $difficulty))
            ->latest('updated_at')
            ->limit(50)
            ->get();

        return view('teacher.exams.questions', [
            'exam' => $exam,
            'bank' => $bank,
            'search' => $search,
            'type' => $type,
            'difficulty' => $difficulty,
            'types' => QuestionType::cases(),
            'difficulties' => QuestionDifficulty::cases(),
        ]);
    }

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);
        abort_if($exam->isFileExam(), 422);

        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'points' => ['nullable', 'numeric', 'min:0.25', 'max:100'],
        ]);

        $question = Question::query()->findOrFail($data['question_id']);
        $this->authorize('attachQuestion', [$exam, $question]);

        $exam->questions()->syncWithoutDetaching([
            $question->id => [
                'points' => $data['points'] ?? null,
                'position' => $exam->nextQuestionPosition(),
            ],
        ]);

        return back()->with('status', 'تمت إضافة السؤال إلى الاختبار.');
    }

    public function update(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        $this->authorize('update', $exam);
        abort_if($exam->isFileExam(), 422);
        abort_unless($exam->questions()->whereKey($question->id)->exists(), 404);

        $data = $request->validate([
            'points' => ['nullable', 'numeric', 'min:0.25', 'max:100'],
            'direction' => ['nullable', Rule::in(['up', 'down'])],
        ]);

        if (array_key_exists('points', $data)) {
            $exam->questions()->updateExistingPivot($question->id, ['points' => $data['points']]);
        }

        if (! empty($data['direction'])) {
            $this->move($exam, $question, $data['direction']);
        }

        return back()->with('status', 'تم تحديث السؤال داخل الاختبار.');
    }

    public function destroy(Exam $exam, Question $question): RedirectResponse
    {
        $this->authorize('update', $exam);
        abort_if($exam->isFileExam(), 422);
        abort_unless($exam->questions()->whereKey($question->id)->exists(), 404);

        $exam->questions()->detach($question->id);
        $this->resequence($exam);

        return back()->with('status', 'تم حذف السؤال من الاختبار.');
    }

    private function move(Exam $exam, Question $question, string $direction): void
    {
        $ordered = $exam->questions()->get();
        $index = $ordered->search(fn (Question $item) => $item->id === $question->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || $swapIndex < 0 || $swapIndex >= $ordered->count()) {
            return;
        }

        $reordered = $ordered->values()->all();
        [$reordered[$index], $reordered[$swapIndex]] = [$reordered[$swapIndex], $reordered[$index]];

        DB::transaction(function () use ($exam, $reordered): void {
            foreach ($reordered as $position => $item) {
                $exam->questions()->updateExistingPivot($item->id, ['position' => $position + 1]);
            }
        });
    }

    private function resequence(Exam $exam): void
    {
        foreach ($exam->questions()->get()->values() as $position => $question) {
            $exam->questions()->updateExistingPivot($question->id, ['position' => $position + 1]);
        }
    }
}
