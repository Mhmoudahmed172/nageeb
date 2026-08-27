<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreLessonCommentRequest;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonView;
use App\Support\ContentAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyCoursesController extends Controller
{
    public function __construct(private readonly ContentAccess $contentAccess) {}

    public function index(): View
    {
        $courses = Course::query()
            ->with('teacher')
            ->where(function ($query) {
                $query->whereHas(
                    'enrollments',
                    fn ($enrollments) => $enrollments->where('student_id', auth()->id())->active(),
                )->orWhere(function ($free) {
                    $free->where('is_free', true)->where('status', 'live');
                });
            })
            ->latest()
            ->get()
            ->filter(fn (Course $course) => $course->studentHasAccess(auth()->user()))
            ->values();

        return view('student.my-courses.index', compact('courses'));
    }

    public function show(Request $request, Course $course): View
    {
        $this->ensureCourseAccess($course);

        $course->load(['semesters.units.lessons.contents.regions', 'teacher']);
        $user = auth()->user();

        $semesters = $course->semesters
            ->filter(fn ($semester) => $this->contentAccess->studentCanAccessSemester($user, $semester))
            ->values();

        $lessons = $semesters
            ->flatMap(fn ($semester) => $semester->units->flatMap->lessons)
            ->filter(fn (Lesson $lesson) => $this->contentAccess->studentCanAccessLesson($user, $lesson))
            ->values();

        $currentLessonId = (int) $request->query('lesson');
        $currentLesson = $lessons->firstWhere('id', $currentLessonId) ?? $lessons->first();

        if ($currentLesson) {
            $currentLesson->setRelation(
                'contents',
                $currentLesson->contents->filter(
                    fn ($content) => $this->contentAccess->studentCanAccessContent($user, $content),
                )->values(),
            );
        }

        $currentIndex = $currentLesson
            ? $lessons->search(fn (Lesson $lesson) => $lesson->id === $currentLesson->id)
            : false;

        $previousLesson = $currentIndex !== false && $currentIndex > 0
            ? $lessons[$currentIndex - 1]
            : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $lessons->count() - 1
            ? $lessons[$currentIndex + 1]
            : null;

        $questions = $currentLesson
            ? Comment::query()
                ->with(['user', 'replies.user'])
                ->where('lesson_id', $currentLesson->id)
                ->whereNull('parent_id')
                ->latest()
                ->get()
            : collect();

        if ($currentLesson) {
            auth()->user()->studentProfile()?->update([
                'last_viewed_lesson_id' => $currentLesson->id,
            ]);

            LessonView::query()->updateOrCreate(
                [
                    'student_id' => auth()->id(),
                    'lesson_id' => $currentLesson->id,
                ],
                ['viewed_at' => now()],
            );
        }

        return view('student.my-courses.show', [
            'course' => $course,
            'lessons' => $lessons,
            'currentLesson' => $currentLesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'questions' => $questions,
        ]);
    }

    public function storeComment(StoreLessonCommentRequest $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->ensureCourseAccess($course);
        abort_unless($lesson->belongsToCourse($course), 404);
        abort_unless($this->contentAccess->studentCanAccessLesson(auth()->user(), $lesson), 403);

        Comment::query()->create([
            'lesson_id' => $lesson->id,
            'user_id' => auth()->id(),
            'message' => $request->validated('message'),
            'parent_id' => null,
        ]);

        return redirect()
            ->route('student.my-courses.show', ['course' => $course, 'lesson' => $lesson->id])
            ->with('status', 'تم إرسال سؤالك.');
    }

    private function ensureCourseAccess(Course $course): void
    {
        abort_unless($this->contentAccess->studentCanAccessCourse(auth()->user(), $course), 403);
    }
}
