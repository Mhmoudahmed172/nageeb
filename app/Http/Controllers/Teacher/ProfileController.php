<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UpdateTeacherProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $profile = auth()->user()->teacherProfile;

        return view('teacher.profile.edit', [
            'user' => auth()->user(),
            'profile' => $profile,
        ]);
    }

    public function update(UpdateTeacherProfileRequest $request): RedirectResponse
    {
        $profile = auth()->user()->teacherProfile()->firstOrCreate(['user_id' => auth()->id()]);
        $data = $request->safe()->only(['bio', 'specialization']);

        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($data);

        return redirect()
            ->route('teacher.profile.edit')
            ->with('status', 'تم حفظ الملف الشخصي.');
    }
}
