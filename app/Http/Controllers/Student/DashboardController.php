<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()->load(['studentProfile.lastViewedLesson.unit.semester.course']);

        $enrolledCount = Enrollment::query()
            ->where('student_id', $user->id)
            ->active()
            ->count();

        return view('dashboards.student', [
            'user' => $user,
            'profile' => $user->studentProfile,
            'enrolledCount' => $enrolledCount,
            'lastLesson' => $user->studentProfile?->lastViewedLesson,
        ]);
    }
}
