@extends('layouts.app')

@section('title', 'لوحة المعلّم — نجيب')

@section('content')
<x-dashboard-layout title="لوحة المعلّم" role-label="المعلّم">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6 max-w-2xl">
            {{ session('status') }}
        </div>
    @endif

    @if (! $profile?->is_verified)
        <div class="nageeb-alert nageeb-alert--warning mb-6 max-w-2xl">
            حسابك قيد المراجعة. سيتم تفعيله من قبل الإدارة قريباً (is_verified = false).
        </div>
    @endif

    <div class="nageeb-card max-w-2xl">
        <h2 class="nageeb-title-section mb-2">مرحباً، {{ $user->name }}</h2>
        <p class="nageeb-text-muted mb-6">أنت مسجّل كمعلّم على المنصة.</p>

        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="nageeb-text-dim mb-1">البريد الإلكتروني</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">رقم الجوال</dt>
                <dd class="font-medium">{{ $user->phone }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">التخصص</dt>
                <dd class="font-medium">{{ $profile?->specialization ?? '—' }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">حالة التحقق</dt>
                <dd>
                    @if ($profile?->is_verified)
                        <span class="nageeb-badge nageeb-badge--support">موثّق</span>
                    @else
                        <span class="nageeb-badge nageeb-badge--secondary">قيد المراجعة</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</x-dashboard-layout>
@endsection
