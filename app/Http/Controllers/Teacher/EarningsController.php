<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function index(): View
    {
        $enrollments = Enrollment::query()
            ->with(['student.studentProfile.region', 'course', 'accessPlan'])
            ->whereHas('course', fn ($query) => $query->where('teacher_id', auth()->id()))
            ->latest('granted_at')
            ->get();

        $rows = $enrollments->map(function (Enrollment $enrollment) {
            return [
                'enrollment' => $enrollment,
                'amount' => (float) ($enrollment->amount_paid ?? 0),
            ];
        });

        $total = $rows->sum('amount');
        $monthTotal = $rows
            ->filter(function (array $row) {
                $grantedAt = $row['enrollment']->granted_at;

                return $grantedAt
                    && $grantedAt->year === now()->year
                    && $grantedAt->month === now()->month;
            })
            ->sum('amount');

        return view('teacher.earnings.index', [
            'rows' => $rows,
            'total' => $total,
            'monthTotal' => $monthTotal,
        ]);
    }
}
