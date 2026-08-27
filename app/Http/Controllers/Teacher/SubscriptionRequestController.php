<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\SubscriptionRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\RejectSubscriptionRequestRequest;
use App\Models\Enrollment;
use App\Models\SubscriptionRequest;
use App\Notifications\SubscriptionApprovedNotification;
use App\Notifications\SubscriptionRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $requests = SubscriptionRequest::query()
            ->with(['student', 'course', 'package', 'accessPlan'])
            ->whereHas('course', fn ($query) => $query->where('teacher_id', auth()->id()))
            ->when(
                in_array($statusFilter, ['pending', 'approved', 'rejected'], true),
                fn ($query) => $query->where('status', $statusFilter),
            )
            ->latest()
            ->get();

        return view('teacher.subscription-requests.index', [
            'requests' => $requests,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function approve(SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        abort_unless($subscriptionRequest->course->teacher_id === auth()->id(), 403);
        abort_unless($subscriptionRequest->status === SubscriptionRequestStatus::Pending, 422);
        abort_unless($subscriptionRequest->access_plan_id, 422);

        $subscriptionRequest->load(['accessPlan.prices', 'student.studentProfile.region']);

        $plan = $subscriptionRequest->accessPlan;
        abort_unless($plan && $plan->course_id === $subscriptionRequest->course_id, 422);

        $region = $subscriptionRequest->student->studentProfile?->region;
        $price = $plan->priceFor($region);
        $duration = $plan->access_duration_days;

        DB::transaction(function () use ($subscriptionRequest, $plan, $region, $price, $duration) {
            $subscriptionRequest->update([
                'status' => SubscriptionRequestStatus::Approved,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            Enrollment::query()->create([
                'student_id' => $subscriptionRequest->student_id,
                'course_id' => $subscriptionRequest->course_id,
                'access_plan_id' => $plan->id,
                'region_id' => $region?->id,
                'amount_paid' => $price?->effectivePrice(),
                'currency' => $price?->currency ?? 'ILS',
                'granted_by' => auth()->id(),
                'granted_at' => now(),
                'starts_at' => now(),
                'expires_at' => $duration ? now()->addDays((int) $duration) : null,
                'status' => 'active',
            ]);
        });

        $subscriptionRequest->load('course');
        $subscriptionRequest->student->notify(new SubscriptionApprovedNotification($subscriptionRequest));

        return redirect()
            ->route('teacher.subscription-requests.index')
            ->with('status', 'تمت الموافقة على الطلب وتم إلحاق الطالب بالمادة.');
    }

    public function reject(RejectSubscriptionRequestRequest $request, SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        abort_unless($subscriptionRequest->course->teacher_id === auth()->id(), 403);
        abort_unless($subscriptionRequest->status === SubscriptionRequestStatus::Pending, 422);

        $subscriptionRequest->update([
            'status' => SubscriptionRequestStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        $subscriptionRequest->load('course');
        $subscriptionRequest->student->notify(new SubscriptionRejectedNotification($subscriptionRequest));

        return redirect()
            ->route('teacher.subscription-requests.index')
            ->with('status', 'تم رفض الطلب.');
    }
}
