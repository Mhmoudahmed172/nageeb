<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()->load('studentProfile');

        return view('dashboards.student', [
            'user' => $user,
            'profile' => $user->studentProfile,
        ]);
    }
}
