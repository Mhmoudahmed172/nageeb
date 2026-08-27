@extends('layouts.app')

@section('title', 'طلبات الاشتراك — نجيب')

@section('content')
<x-dashboard-layout title="طلبات الاشتراك" role-label="المعلّم" active-menu="subscription-requests">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    @php
        $tabs = [
            '' => 'الكل',
            'pending' => 'معلّقة',
            'approved' => 'موافق عليها',
            'rejected' => 'مرفوضة',
        ];
    @endphp

    <nav class="flex flex-wrap gap-2 mb-6" aria-label="فلترة الحالة">
        @foreach ($tabs as $value => $label)
            <a
                href="{{ route('teacher.subscription-requests.index', $value === '' ? [] : ['status' => $value]) }}"
                @class([
                    'px-4 py-2 text-sm font-medium',
                    'bg-primary text-text-inverse' => ($statusFilter ?? '') === $value,
                    'text-text hover:bg-primary-muted' => ($statusFilter ?? '') !== $value,
                ])
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="nageeb-card nageeb-table-wrap" x-data="{ receiptUrl: null }" dir="rtl">
        @if ($requests->isEmpty())
            <x-empty-state title="لا توجد طلبات اشتراك." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">الطالب</th>
                        <th class="py-3 px-2 font-medium">المادة</th>
                        <th class="py-3 px-2 font-medium">خطة الوصول</th>
                        <th class="py-3 px-2 font-medium">تاريخ الطلب</th>
                        <th class="py-3 px-2 font-medium">الإيصال</th>
                        <th class="py-3 px-2 font-medium">الحالة</th>
                        <th class="py-3 px-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $item)
                        <tr class="border-b border-border last:border-0 align-top">
                            <td class="py-3 px-2">{{ $item->student->name }}</td>
                            <td class="py-3 px-2">{{ $item->course->title }}</td>
                            <td class="py-3 px-2">{{ $item->accessPlan?->title ?? $item->package?->name }}</td>
                            <td class="py-3 px-2">{{ $item->created_at->format('Y/m/d') }}</td>
                            <td class="py-3 px-2">
                                @if ($item->receiptIsPdf())
                                    <a href="{{ $item->receiptUrl() }}" target="_blank" rel="noopener">عرض الإيصال</a>
                                @else
                                    <button
                                        type="button"
                                        class="nageeb-btn nageeb-btn--outline text-sm py-1 px-3"
                                        @click="receiptUrl = '{{ $item->receiptUrl() }}'; isPdf = false"
                                    >
                                        عرض الإيصال
                                    </button>
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                <span class="{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span>
                            </td>
                            <td class="py-3 px-2">
                                @if ($item->status === \App\Enums\SubscriptionRequestStatus::Pending)
                                    <div class="flex flex-col gap-2 min-w-52">
                                        <form method="POST" action="{{ route('teacher.subscription-requests.approve', $item) }}">
                                            @csrf
                                            <button type="submit" class="nageeb-btn nageeb-btn--primary text-sm py-2 px-3 w-full">موافقة</button>
                                        </form>
                                        <form method="POST" action="{{ route('teacher.subscription-requests.reject', $item) }}" class="grid gap-2">
                                            @csrf
                                            <input type="text" name="rejection_reason" class="nageeb-input" placeholder="سبب الرفض (اختياري)">
                                            @error('rejection_reason')
                                                <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                                            @enderror
                                            <button type="submit" class="nageeb-btn nageeb-btn--secondary text-sm py-2 px-3">رفض</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-text/70 p-4"
            x-show="receiptUrl"
            x-cloak
            dir="rtl"
            @keydown.escape.window="receiptUrl = null"
        >
            <button type="button" class="absolute inset-0" aria-label="إغلاق" @click="receiptUrl = null"></button>
            <div class="nageeb-modal relative bg-surface max-w-3xl w-full p-4">
                <div class="flex justify-start mb-3">
                    <button type="button" class="nageeb-btn nageeb-btn--outline text-sm py-1 px-3" @click="receiptUrl = null">إغلاق</button>
                </div>
                <img :src="receiptUrl" alt="إيصال الدفع" class="w-full h-auto">
            </div>
        </div>
    </div>
</x-dashboard-layout>
@endsection
