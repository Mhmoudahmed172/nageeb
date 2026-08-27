<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrolledStudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $courseId = $request->query('course_id');
        $region = $request->query('region');
        $regionId = is_numeric($region) ? (int) $region : Region::query()->where('code', $region)->value('id');

        $courses = Course::query()->forTeacher(auth()->id())->orderBy('title')->get();
        $regions = Region::query()->active()->get();

        $enrollments = Enrollment::query()
            ->with(['student.studentProfile.region', 'course'])
            ->whereHas('course', fn ($query) => $query->where('teacher_id', auth()->id()))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', fn ($student) => $student->where('name', 'like', '%'.$search.'%'));
            })
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->when($regionId, fn ($query) => $query->whereHas(
                'student.studentProfile',
                fn ($profile) => $profile->where('region_id', $regionId),
            ))
            ->latest('granted_at')
            ->get();

        return view('teacher.enrolled-students.index', [
            'enrollments' => $enrollments,
            'courses' => $courses,
            'regions' => $regions,
            'search' => $search,
            'courseId' => $courseId,
            'region' => $region,
        ]);
    }
}
