<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Account\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(): View
    {
        return view('account.settings', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        auth()->user()->update($request->validated());

        return back()->with('status', 'تم حفظ بيانات الحساب.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        auth()->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('status', 'تم تحديث كلمة المرور.');
    }
}
