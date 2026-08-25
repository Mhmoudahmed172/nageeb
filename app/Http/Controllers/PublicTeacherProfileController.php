<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\User;
use Illuminate\View\View;

class PublicTeacherProfileController extends Controller
{
    public function show(User $teacher): View
    {
        abort_unless($teacher->isTeacher(), 404);

        $teacher->load('teacherProfile');

        $courses = $teacher->courses()
            ->where('status', CourseStatus::Live)
            ->latest()
            ->get();

        return view('teachers.show', compact('teacher', 'courses'));
    }
}
