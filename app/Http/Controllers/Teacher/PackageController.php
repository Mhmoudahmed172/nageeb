<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StorePackageRequest;
use App\Http\Requests\Teacher\UpdatePackageRequest;
use App\Models\Course;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorizeCourse($course);

        $packages = $course->packages()->latest()->get();

        return view('teacher.packages.index', compact('course', 'packages'));
    }

    public function store(StorePackageRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $course->packages()->create($request->validated());

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تمت إضافة الباقة.');
    }

    public function update(UpdatePackageRequest $request, Course $course, SubscriptionPackage $package): RedirectResponse
    {
        $this->authorizeCourse($course);
        $this->authorizePackage($course, $package);

        $package->update($request->validated());

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تم تحديث الباقة.');
    }

    public function destroy(Course $course, SubscriptionPackage $package): RedirectResponse
    {
        $this->authorizeCourse($course);
        $this->authorizePackage($course, $package);

        $package->delete();

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تم حذف الباقة.');
    }

    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->teacher_id === auth()->id(), 403);
    }

    private function authorizePackage(Course $course, SubscriptionPackage $package): void
    {
        abort_unless($package->course_id === $course->id, 404);
    }
}
