<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayoutRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Support\EnrollmentRevenue;
use Illuminate\View\View;

class OverviewController extends Controller
{
    public function index(): View
    {
        $unverifiedTeachers = User::query()
            ->where('role', UserRole::Teacher)
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', false))
            ->with('teacherProfile')
            ->latest()
            ->take(5)
            ->get();

        $pendingPayouts = PayoutRequest::query()
            ->with('teacher')
            ->where('status', PayoutRequestStatus::Pending)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboards.admin', [
            'user' => auth()->user(),
            'teachersCount' => User::query()->where('role', UserRole::Teacher)->count(),
            'studentsCount' => User::query()->where('role', UserRole::Student)->count(),
            'coursesCount' => Course::query()->count(),
            'totalEarnings' => EnrollmentRevenue::total(),
            'unverifiedTeachers' => $unverifiedTeachers,
            'pendingPayouts' => $pendingPayouts,
        ]);
    }
}
