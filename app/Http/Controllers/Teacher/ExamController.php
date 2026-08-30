<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveExamRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Region;
use App\Support\MediaStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private readonly MediaStore $media) {}
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $teacherId = auth()->id();
        $search = trim((string) $request->query('search', ''));
        $courseId = $request->query('course_id');
        $status = $request->query('status');

        $exams = Exam::query()
            ->forTeacher($teacherId)
            ->with(['course', 'unit', 'lesson'])
            ->withCount(['questions', 'attempts'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('updated_at')
            ->get();

        $allExams = Exam::query()->forTeacher($teacherId)->get(['id', 'status']);
        $attemptStats = ExamAttempt::query()
            ->whereIn('exam_id', $allExams->pluck('id'))
            ->whereNotNull('submitted_at')
            ->get(['percentage']);

        return view('teacher.exams.index', [
            'exams' => $exams,
            'courses' => Course::query()->forTeacher($teacherId)->orderBy('title')->get(),
            'search' => $search,
            'courseId' => $courseId,
            'status' => $status,
            'stats' => [
                'total' => $allExams->count(),
                'published' => $allExams->where('status', ExamStatus::Published)->count(),
                'drafts' => $allExams->where('status', ExamStatus::Draft)->count(),
                'attempts' => $attemptStats->count(),
                'average' => $attemptStats->isEmpty() ? null : round((float) $attemptStats->avg('percentage'), 1),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Exam::class);

        return view('teacher.exams.create', $this->formData());
    }

    public function store(SaveExamRequest $request): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $exam = Exam::query()->create([
            ...$request->examAttributes(),
            ...($request->paperFileAttributes() ?? []),
            'teacher_id' => auth()->id(),
        ]);
        $exam->regions()->sync($request->regionIds());

        if ($exam->isFileExam()) {
            return redirect()
                ->route('teacher.exams.show', $exam)
                ->with('status', 'تم إنشاء الاختبار المرفق.');
        }

        return redirect()
            ->route('teacher.exams.questions.index', $exam)
            ->with('status', 'تم إنشاء الاختبار. أضف الأسئلة الآن.');
    }

    public function show(Exam $exam): View
    {
        $this->authorize('view', $exam);

        $exam->load(['course', 'semester', 'unit', 'lesson', 'questions.options', 'regions']);

        return view('teacher.exams.show', [
            'exam' => $exam,
            'attemptsCount' => $exam->attempts()->count(),
        ]);
    }

    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        $exam->load('regions');

        return view('teacher.exams.edit', [...$this->formData(), 'exam' => $exam]);
    }

    public function update(SaveExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $previousPath = $exam->file_path;
        $previousDisk = $exam->file_disk;

        $attributes = $request->examAttributes();
        $paper = $request->paperFileAttributes();

        if ($paper) {
            $attributes = [...$attributes, ...$paper];
        } elseif (! $request->isFileExam()) {
            $attributes = [
                ...$attributes,
                'file_disk' => null,
                'file_path' => null,
                'file_original_name' => null,
                'file_mime' => null,
                'file_size' => null,
                'file_uploaded_at' => null,
            ];
        }

        $exam->update($attributes);
        $exam->regions()->sync($request->regionIds());

        if ($paper || ! $request->isFileExam()) {
            $this->media->delete($previousPath, $previousDisk);
        }

        return redirect()
            ->route('teacher.exams.show', $exam)
            ->with('status', 'تم حفظ الاختبار.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);

        if ($exam->attempts()->exists()) {
            return back()->with('error', 'لا يمكن حذف اختبار له محاولات مسجّلة. يمكنك أرشفته بدلًا من ذلك.');
        }

        $this->media->delete($exam->file_path, $exam->file_disk);
        $exam->delete();

        return redirect()
            ->route('teacher.exams.index')
            ->with('status', 'تم حذف الاختبار.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'courses' => Course::query()
                ->forTeacher(auth()->id())
                ->with(['semesters.units.lessons'])
                ->orderBy('title')
                ->get(),
            'regions' => Region::query()->active()->get(),
        ];
    }
}
