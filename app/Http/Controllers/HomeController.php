<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $liveCourses = Course::query()
            ->with(['teacher.teacherProfile'])
            ->withCount('enrollments')
            ->where('status', CourseStatus::Live)
            ->latest()
            ->get();

        $heroCourses = $liveCourses->take(3);
        $exploreCourses = $liveCourses->skip(3)->take(6);

        $teachers = User::query()
            ->where('role', UserRole::Teacher)
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true))
            ->with('teacherProfile')
            ->withCount([
                'courses as live_courses_count' => fn ($query) => $query->where('status', CourseStatus::Live),
            ])
            ->orderByDesc('live_courses_count')
            ->take(5)
            ->get();

        $roleCounts = User::query()
            ->selectRaw('role, count(*) as aggregate')
            ->whereIn('role', [UserRole::Student, UserRole::Teacher])
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        return view('welcome', [
            'heroCourses' => $heroCourses,
            'exploreCourses' => $exploreCourses,
            'teachers' => $teachers,
            'studentsCount' => (int) ($roleCounts[UserRole::Student->value] ?? 0),
            'teachersCount' => (int) ($roleCounts[UserRole::Teacher->value] ?? 0),
            'liveCoursesCount' => $liveCourses->count(),
        ]);
    }
}
