<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreSubscriptionRequestRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function create(Course $course): View
    {
        $course->load('teacher', 'packages');
        $region = auth()->user()->studentProfile?->region;

        return view('student.subscribe', compact('course', 'region'));
    }

    public function store(StoreSubscriptionRequestRequest $request, Course $course): RedirectResponse
    {
        $path = $request->file('receipt')->store('receipts', 'public');

        auth()->user()->subscriptionRequests()->create([
            'course_id' => $course->id,
            'package_id' => $request->integer('package_id'),
            'receipt_image_path' => $path,
        ]);

        return redirect()->route('courses.subscribe.confirmation', $course);
    }

    public function confirmation(Course $course): View
    {
        return view('student.subscribe-confirmation', compact('course'));
    }
}
