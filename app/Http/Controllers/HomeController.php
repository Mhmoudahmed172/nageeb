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

        return view('welcome', [
            'heroCourses' => $heroCourses,
            'exploreCourses' => $exploreCourses,
            'teachers' => $teachers,
            'studentsCount' => User::query()->where('role', UserRole::Student)->count(),
            'teachersCount' => User::query()->where('role', UserRole::Teacher)->count(),
            'liveCoursesCount' => $liveCourses->count(),
        ]);
    }
}
