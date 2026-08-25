<?php

namespace App\Support;

use App\Enums\StudentRegion;
use App\Enums\SubscriptionRequestStatus;
use App\Models\Enrollment;
use App\Models\SubscriptionRequest;

class EnrollmentRevenue
{
    public static function total(?int $teacherId = null): float
    {
        $enrollments = Enrollment::query()
            ->with('student.studentProfile')
            ->when(
                $teacherId,
                fn ($query) => $query->whereHas('course', fn ($course) => $course->where('teacher_id', $teacherId)),
            )
            ->get();

        if ($enrollments->isEmpty()) {
            return 0;
        }

        $packagesByKey = SubscriptionRequest::query()
            ->with('package')
            ->where('status', SubscriptionRequestStatus::Approved)
            ->where(function ($query) use ($enrollments) {
                foreach ($enrollments as $enrollment) {
                    $query->orWhere(function ($inner) use ($enrollment) {
                        $inner->where('student_id', $enrollment->student_id)
                            ->where('course_id', $enrollment->course_id);
                    });
                }
            })
            ->latest('reviewed_at')
            ->get()
            ->unique(fn (SubscriptionRequest $request) => $request->student_id.'-'.$request->course_id)
            ->mapWithKeys(fn (SubscriptionRequest $request) => [
                $request->student_id.'-'.$request->course_id => $request->package,
            ]);

        return (float) $enrollments->sum(function (Enrollment $enrollment) use ($packagesByKey) {
            $package = $packagesByKey->get($enrollment->student_id.'-'.$enrollment->course_id);

            if (! $package) {
                return 0;
            }

            $region = $enrollment->student->studentProfile?->region ?? StudentRegion::Gaza;

            return (float) $package->priceFor($region);
        });
    }
}
