@extends('layouts.app')

@section('title', 'التسويات والسحوبات — نجيب')

@section('content')
<x-dashboard-layout title="التسويات والسحوبات" role-label="المعلّم" active-menu="payouts">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('teacher.payouts.store') }}" class="nageeb-card max-w-xl grid gap-4 mb-8">
        @csrf
        <h2 class="nageeb-title-section">طلب سحب جديد</h2>
        <x-form-input label="المبلغ" name="amount" type="number" required step="0.01" min="1" />
        <x-form-textarea label="بيانات الحساب البنكي" name="bank_details" required />
        <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">إرسال الطلب</button>
    </form>

    <div class="nageeb-card nageeb-table-wrap">
        <h2 class="nageeb-title-section mb-4">طلبات سابقة</h2>
        @if ($payouts->isEmpty())
            <x-empty-state title="لا توجد طلبات سحب." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">المبلغ</th>
                        <th class="py-3 px-2 font-medium">الحالة</th>
                        <th class="py-3 px-2 font-medium">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payouts as $payout)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2 price">{{ number_format($payout->amount, 2) }} ₪</td>
                            <td class="py-3 px-2">{{ $payout->status->label() }}</td>
                            <td class="py-3 px-2">{{ $payout->created_at->format('Y/m/d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
