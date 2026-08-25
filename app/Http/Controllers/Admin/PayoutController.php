<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayoutRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(): View
    {
        $payouts = PayoutRequest::query()
            ->with('teacher')
            ->where('status', PayoutRequestStatus::Pending)
            ->latest()
            ->get();

        return view('admin.payouts.index', compact('payouts'));
    }

    public function markAsSettled(PayoutRequest $payoutRequest): RedirectResponse
    {
        abort_unless($payoutRequest->status === PayoutRequestStatus::Pending, 422);

        $payoutRequest->update([
            'status' => PayoutRequestStatus::Settled,
        ]);

        return redirect()
            ->route('admin.payouts.index')
            ->with('status', 'تم تعليم طلب السحب كمسوّى.');
    }
}
