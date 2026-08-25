<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\View\View;

class CourseCatalogController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with('teacher.teacherProfile')
            ->where('status', CourseStatus::Live)
            ->latest()
            ->get();

        return view('courses.index', compact('courses'));
    }
}
