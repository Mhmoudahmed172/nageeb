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
            ->with(['student', 'course', 'package'])
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
        $this->authorizeRequest($subscriptionRequest);
        abort_unless($subscriptionRequest->status === SubscriptionRequestStatus::Pending, 422);

        DB::transaction(function () use ($subscriptionRequest) {
            $subscriptionRequest->update([
                'status' => SubscriptionRequestStatus::Approved,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            Enrollment::query()->firstOrCreate(
                [
                    'student_id' => $subscriptionRequest->student_id,
                    'course_id' => $subscriptionRequest->course_id,
                ],
                [
                    'granted_by' => auth()->id(),
                    'granted_at' => now(),
                    'expires_at' => null,
                ],
            );
        });

        $subscriptionRequest->load('course');
        $subscriptionRequest->student->notify(new SubscriptionApprovedNotification($subscriptionRequest));

        return redirect()
            ->route('teacher.subscription-requests.index')
            ->with('status', 'تمت الموافقة على الطلب وتم إلحاق الطالب بالمادة.');
    }

    public function reject(RejectSubscriptionRequestRequest $request, SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $this->authorizeRequest($subscriptionRequest);
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

    private function authorizeRequest(SubscriptionRequest $subscriptionRequest): void
    {
        abort_unless($subscriptionRequest->course->teacher_id === auth()->id(), 403);
    }
}
