<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register-student', [
            'regions' => \App\Models\Region::query()->active()->get(),
        ]);
    }

    public function store(StudentRegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
            'role' => UserRole::Student,
            'password' => $request->string('password')->toString(),
        ]);

        $user->studentProfile()->create([
            'region_id' => $request->integer('region_id'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('student.dashboard');
    }
}
