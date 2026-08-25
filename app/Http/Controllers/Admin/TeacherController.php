<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::query()
            ->where('role', UserRole::Teacher)
            ->with('teacherProfile')
            ->latest()
            ->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function verify(User $teacher): RedirectResponse
    {
        abort_unless($teacher->isTeacher(), 404);

        $profile = $teacher->teacherProfile()->firstOrCreate(['user_id' => $teacher->id]);
        $profile->update(['is_verified' => true]);

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', 'تم توثيق حساب المعلّم.');
    }
}
