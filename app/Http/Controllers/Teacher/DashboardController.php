<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()->load('teacherProfile');

        return view('dashboards.teacher', [
            'user' => $user,
            'profile' => $user->teacherProfile,
        ]);
    }
}
