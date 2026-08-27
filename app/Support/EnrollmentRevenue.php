<?php

namespace App\Support;

use App\Models\Enrollment;
use Carbon\CarbonInterface;

class EnrollmentRevenue
{
    public static function total(?int $teacherId = null, ?CarbonInterface $from = null, ?CarbonInterface $to = null): float
    {
        return (float) Enrollment::query()
            ->when(
                $teacherId,
                fn ($query) => $query->whereHas('course', fn ($course) => $course->where('teacher_id', $teacherId)),
            )
            ->when($from, fn ($query) => $query->where('granted_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('granted_at', '<=', $to))
            ->sum('amount_paid');
    }
}
