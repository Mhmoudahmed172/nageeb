<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreLessonCommentRequest;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyCoursesController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with('teacher')
            ->whereHas(
                'enrollments',
                fn ($query) => $query->where('student_id', auth()->id())->active(),
            )
            ->latest()
            ->get();

        return view('student.my-courses.index', compact('courses'));
    }

    public function show(Request $request, Course $course): View
    {
        $this->ensureActiveEnrollment($course);

        $course->load(['units.lessons.attachments', 'teacher']);

        $lessons = $course->units->flatMap->lessons->values();
        $currentLessonId = (int) $request->query('lesson');
        $currentLesson = $lessons->firstWhere('id', $currentLessonId) ?? $lessons->first();

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
        $this->ensureActiveEnrollment($course);
        abort_unless($lesson->unit->course_id === $course->id, 404);

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

    private function ensureActiveEnrollment(Course $course): void
    {
        abort_unless(
            Enrollment::query()
                ->where('student_id', auth()->id())
                ->where('course_id', $course->id)
                ->active()
                ->exists(),
            403,
        );
    }
}
