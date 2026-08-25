<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreCourseRequest;
use App\Http\Requests\Teacher\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->forTeacher(auth()->id())
            ->latest()
            ->get();

        return view('teacher.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('teacher.courses.create');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::query()->create([
            ...$request->validated(),
            'teacher_id' => auth()->id(),
        ]);

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تم إنشاء المادة. يمكنك إضافة باقات الاشتراك.');
    }

    public function edit(Course $course): View
    {
        $this->authorizeCourse($course);

        return view('teacher.courses.edit', compact('course'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);
        $course->update($request->validated());

        return redirect()
            ->route('teacher.courses.edit', $course)
            ->with('status', 'تم حفظ تعديلات المادة.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);
        $course->delete();

        return redirect()
            ->route('teacher.courses.index')
            ->with('status', 'تم حذف المادة.');
    }

    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->teacher_id === auth()->id(), 403);
    }
}
