<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\StudentRegion;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrolledStudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $courseId = $request->query('course_id');
        $region = $request->query('region');

        $courses = Course::query()->forTeacher(auth()->id())->orderBy('title')->get();

        $enrollments = Enrollment::query()
            ->with(['student.studentProfile', 'course'])
            ->whereHas('course', fn ($query) => $query->where('teacher_id', auth()->id()))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', fn ($student) => $student->where('name', 'like', '%'.$search.'%'));
            })
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->when(
                in_array($region, [StudentRegion::Gaza->value, StudentRegion::WestBankAbroad->value], true),
                fn ($query) => $query->whereHas('student.studentProfile', fn ($profile) => $profile->where('region', $region)),
            )
            ->latest('granted_at')
            ->get();

        return view('teacher.enrolled-students.index', [
            'enrollments' => $enrollments,
            'courses' => $courses,
            'search' => $search,
            'courseId' => $courseId,
            'region' => $region,
        ]);
    }
}
