<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StorePayoutRequestRequest;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(): View
    {
        $payouts = PayoutRequest::query()
            ->where('teacher_id', auth()->id())
            ->latest()
            ->get();

        return view('teacher.payouts.index', compact('payouts'));
    }

    public function store(StorePayoutRequestRequest $request): RedirectResponse
    {
        PayoutRequest::query()->create([
            ...$request->validated(),
            'teacher_id' => auth()->id(),
        ]);

        return redirect()
            ->route('teacher.payouts.index')
            ->with('status', 'تم إرسال طلب السحب.');
    }
}
