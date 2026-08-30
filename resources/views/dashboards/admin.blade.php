@extends('layouts.app')

@section('title', 'لوحة الإدارة — نجيب')

@section('content')
<x-dashboard-layout title="نظرة عامة" role-label="المدير" active-menu="dashboard">
    <x-reveal class="mb-8">
        <h2 class="nageeb-heading-1">صحة المنصة</h2>
        <p class="nageeb-text-muted mt-2">أرقام حيّة للمعلمين والطلاب والمواد وأرباح المنصة.</p>
    </x-reveal>

    <x-reveal stagger class="nageeb-kpi-strip mb-10">
        <x-dashboard-stat class="nageeb-reveal-item" label="المعلمون" :value="$teachersCount" />
        <x-dashboard-stat class="nageeb-reveal-item" label="الطلاب" :value="$studentsCount" />
        <x-dashboard-stat class="nageeb-reveal-item" label="المواد" :value="$coursesCount" />
        <x-dashboard-stat class="nageeb-reveal-item" label="أرباح المنصة" :value="number_format($totalEarnings, 2).' ₪'" />
    </x-reveal>

    <x-reveal stagger class="grid gap-6 lg:grid-cols-2 mb-10">
        <x-card class="nageeb-reveal-item" title="معلّمون بانتظار التوثيق">
            <x-slot:actions>
                <a href="{{ route('admin.teachers.index') }}" class="text-sm font-medium">كل المعلمين</a>
            </x-slot:actions>
            @if ($unverifiedTeachers->isEmpty())
                <x-empty-state title="لا يوجد معلّمون بانتظار التوثيق." />
            @else
                <ul class="divide-y divide-border">
                    @foreach ($unverifiedTeachers as $teacher)
                        <li class="py-3 flex items-center justify-between gap-3 text-sm">
                            <span class="flex items-center gap-3 min-w-0">
                                <img src="{{ \App\Support\NageebVisual::teacherPhoto($teacher) }}" alt="" class="nageeb-avatar nageeb-avatar--sm">
                                <span class="truncate">{{ $teacher->name }}</span>
                            </span>
                            <span class="nageeb-text-muted">{{ $teacher->teacherProfile?->specialization }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
        <x-card class="nageeb-reveal-item" title="سحوبات معلّقة">
            <x-slot:actions>
                <a href="{{ route('admin.payouts.index') }}" class="text-sm font-medium">كل الطلبات</a>
            </x-slot:actions>
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
        </x-card>
    </x-reveal>

    <x-reveal>
        <x-card title="حسابك">
            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
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
        </x-card>
    </x-reveal>
</x-dashboard-layout>
@endsection
