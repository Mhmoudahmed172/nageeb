<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseManageController extends Controller
{
    public function overview(Course $course): View
    {
        $this->authorize('update', $course);

        $course->loadCount(['units', 'enrollments', 'accessPlans', 'packages']);
        $course->setAttribute(
            'lessons_count',
            Lesson::query()
                ->whereHas('unit.semester', fn ($query) => $query->where('course_id', $course->id))
                ->count(),
        );

        return view('teacher.courses.manage.overview', compact('course'));
    }

    public function students(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['enrollments.student.studentProfile.region']);

        return view('teacher.courses.manage.students', compact('course'));
    }

    public function analytics(Course $course): View
    {
        $this->authorize('update', $course);

        return view('teacher.courses.manage.analytics', compact('course'));
    }

    public function settings(Course $course): View
    {
        $this->authorize('update', $course);

        return view('teacher.courses.manage.settings', compact('course'));
    }

    public function preview(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['semesters.units.lessons']);

        return view('teacher.courses.manage.preview', compact('course'));
    }

    public function togglePublish(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update([
            'status' => $course->status === CourseStatus::Live
                ? CourseStatus::Draft
                : CourseStatus::Live,
        ]);

        $message = $course->status === CourseStatus::Live
            ? 'تم نشر المادة.'
            : 'تم إلغاء النشر. المادة أصبحت مسودة.';

        return redirect()
            ->back()
            ->with('status', $message);
    }
}
