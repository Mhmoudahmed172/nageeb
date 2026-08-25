<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\StudentRegion;
use App\Enums\SubscriptionRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SubscriptionRequest;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function index(): View
    {
        $enrollments = Enrollment::query()
            ->with(['student.studentProfile', 'course'])
            ->whereHas('course', fn ($query) => $query->where('teacher_id', auth()->id()))
            ->latest('granted_at')
            ->get();

        $packagesByKey = $this->approvedPackages($enrollments);

        $rows = $enrollments->map(function (Enrollment $enrollment) use ($packagesByKey) {
            $amount = $this->amountFor($enrollment, $packagesByKey);

            return [
                'enrollment' => $enrollment,
                'amount' => $amount,
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

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return Collection<string, \App\Models\SubscriptionPackage>
     */
    private function approvedPackages(Collection $enrollments): Collection
    {
        if ($enrollments->isEmpty()) {
            return collect();
        }

        return SubscriptionRequest::query()
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
    }

    /**
     * @param  Collection<string, \App\Models\SubscriptionPackage>  $packagesByKey
     */
    private function amountFor(Enrollment $enrollment, Collection $packagesByKey): float
    {
        $package = $packagesByKey->get($enrollment->student_id.'-'.$enrollment->course_id);

        if (! $package) {
            return 0;
        }

        $region = $enrollment->student->studentProfile?->region ?? StudentRegion::Gaza;

        return (float) $package->priceFor($region);
    }
}
