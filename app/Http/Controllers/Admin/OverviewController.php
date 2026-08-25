<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Support\EnrollmentRevenue;
use Illuminate\View\View;

class OverviewController extends Controller
{
    public function index(): View
    {
        return view('admin.overview.index', [
            'teachersCount' => User::query()->where('role', UserRole::Teacher)->count(),
            'studentsCount' => User::query()->where('role', UserRole::Student)->count(),
            'coursesCount' => Course::query()->count(),
            'totalEarnings' => EnrollmentRevenue::total(),
        ]);
    }
}
