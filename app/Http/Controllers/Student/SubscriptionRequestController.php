<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreSubscriptionRequestRequest;
use App\Models\AccessPlan;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function create(Course $course): View|RedirectResponse
    {
        if ($course->isFreeForStudents()) {
            return redirect()->route('student.my-courses.show', $course);
        }

        $course->load(['teacher', 'accessPlans.prices.region', 'accessPlans.semesters']);
        $region = auth()->user()->studentProfile?->region;

        return view('student.subscribe', compact('course', 'region'));
    }

    public function store(StoreSubscriptionRequestRequest $request, Course $course): RedirectResponse
    {
        abort_if($course->isFreeForStudents(), 422);

        $plan = AccessPlan::query()
            ->where('course_id', $course->id)
            ->findOrFail($request->integer('access_plan_id'));

        $path = $request->file('receipt')->store('receipts', 'public');

        auth()->user()->subscriptionRequests()->create([
            'course_id' => $course->id,
            'package_id' => null,
            'access_plan_id' => $plan->id,
            'receipt_image_path' => $path,
        ]);

        return redirect()->route('courses.subscribe.confirmation', $course);
    }

    public function confirmation(Course $course): View
    {
        return view('student.subscribe-confirmation', compact('course'));
    }
}
