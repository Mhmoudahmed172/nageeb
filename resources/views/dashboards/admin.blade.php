@extends('layouts.app')

@section('title', 'لوحة الإدارة — نجيب')

@section('content')
<x-dashboard-layout title="نظرة عامة" role-label="المدير" active-menu="dashboard">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-10">
        <div class="p-5 border border-border bg-primary-muted">
            <p class="text-sm mb-1">المعلمون</p>
            <p class="text-3xl font-bold price">{{ $teachersCount }}</p>
        </div>
        <div class="p-5 border border-border bg-support-muted">
            <p class="text-sm mb-1">الطلاب</p>
            <p class="text-3xl font-bold price">{{ $studentsCount }}</p>
        </div>
        <div class="p-5 border border-border bg-surface">
            <p class="text-sm mb-1">المواد</p>
            <p class="text-3xl font-bold price">{{ $coursesCount }}</p>
        </div>
        <div class="p-5 border border-border bg-secondary-muted">
            <p class="text-sm mb-1">أرباح المنصة</p>
            <p class="text-3xl font-bold price">{{ number_format($totalEarnings, 2) }} ₪</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mb-10">
        <div class="nageeb-card">
            <div class="flex justify-between gap-3 mb-4">
                <h2 class="font-semibold">معلّمون بانتظار التوثيق</h2>
                <a href="{{ route('admin.teachers.index') }}" class="text-sm font-medium">كل المعلمين</a>
            </div>
            @if ($unverifiedTeachers->isEmpty())
                <x-empty-state title="لا يوجد معلّمون بانتظار التوثيق." />
            @else
                <ul class="divide-y divide-border">
                    @foreach ($unverifiedTeachers as $teacher)
                        <li class="py-3 flex justify-between gap-3 text-sm">
                            <span>{{ $teacher->name }}</span>
                            <span class="nageeb-text-muted">{{ $teacher->teacherProfile?->specialization }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="nageeb-card">
            <div class="flex justify-between gap-3 mb-4">
                <h2 class="font-semibold">سحوبات معلّقة</h2>
                <a href="{{ route('admin.payouts.index') }}" class="text-sm font-medium">كل الطلبات</a>
            </div>
            @if ($pendingPayouts->isEmpty())
                <x-empty-state title="لا توجد طلبات سحب معلّقة." />
            @else
                <ul class="divide-y divide-border">
                    @foreach ($pendingPayouts as $payout)
                        <li class="py-3 flex justify-between gap-3 text-sm">
                            <span>{{ $payout->teacher->name }}</span>
                            <span class="price">{{ number_format($payout->amount, 2) }} ₪</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="nageeb-card max-w-xl text-sm">
        <h2 class="font-medium mb-4">حسابك</h2>
        <dl class="grid gap-3 sm:grid-cols-2">
            <div>
                <dt class="nageeb-text-dim mb-1">البريد</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">الجوال</dt>
                <dd>{{ $user->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">الدور</dt>
                <dd><span class="nageeb-badge nageeb-badge--primary">{{ $user->role->label() }}</span></dd>
            </div>
        </dl>
    </div>
</x-dashboard-layout>
@endsection
