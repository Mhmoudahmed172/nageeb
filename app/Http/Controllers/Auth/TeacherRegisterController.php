<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TeacherRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register-teacher');
    }

    public function store(TeacherRegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
            'role' => UserRole::Teacher,
            'password' => $request->string('password')->toString(),
        ]);

        $user->teacherProfile()->create([
            'specialization' => $request->string('specialization')->toString(),
            'is_verified' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()
            ->route('teacher.dashboard')
            ->with('status', 'تم إنشاء حسابك بنجاح. سيتم تفعيله بعد مراجعة الإدارة.');
    }
}
