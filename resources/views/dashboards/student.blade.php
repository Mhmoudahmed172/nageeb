@extends('layouts.app')

@section('title', 'لوحة الطالب — نجيب')

@section('content')
<x-dashboard-layout title="لوحة الطالب" role-label="الطالب" active-menu="dashboard">
    <div class="flex flex-col lg:flex-row gap-4 mb-10">
        <div class="p-6 border border-border bg-support-muted flex-1">
            <p class="text-sm mb-1">موادك المفعّلة</p>
            <p class="text-4xl font-bold price mb-2">{{ $enrolledCount }}</p>
            <p class="text-xs nageeb-text-dim">التحاقات سارية (بدون تاريخ انتهاء أو لم ينتهِ بعد)</p>
            @if ($enrolledCount > 0)
                <a href="{{ route('student.my-courses.index') }}" class="inline-block mt-4 text-sm font-medium">فتح موادي</a>
            @endif
        </div>
        <div class="p-6 border border-border bg-primary-muted flex-[1.4]">
            <p class="text-sm mb-2">آخر درس فتحته</p>
            @if ($lastLesson)
                <p class="text-xl font-semibold mb-1">{{ $lastLesson->title }}</p>
                <p class="text-sm nageeb-text-muted mb-4">{{ $lastLesson->unit?->course?->title }}</p>
                <a href="{{ route('student.my-courses.show', ['course' => $lastLesson->unit->semester->course, 'lesson' => $lastLesson->id]) }}" class="text-sm font-medium">متابعة الدرس</a>
            @else
                <x-empty-state title="لم تفتح درساً بعد.">
                    ادخل إلى مادة ملتحق بها ليُحفظ آخر درس هنا.
                </x-empty-state>
            @endif
        </div>
    </div>

    <p class="mb-10">
        <a href="{{ route('courses.index') }}" class="nageeb-btn nageeb-btn--secondary">تصفح مواد جديدة</a>
    </p>

    <div class="nageeb-card max-w-xl text-sm">
        <h2 class="font-medium mb-4">حسابك</h2>
        <dl class="grid gap-3 sm:grid-cols-2">
            <div>
                <dt class="nageeb-text-dim mb-1">البريد</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">الجوال</dt>
                <dd>{{ $user->phone }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">المنطقة</dt>
                <dd>{{ $profile?->region?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">الدور</dt>
                <dd><span class="nageeb-badge nageeb-badge--primary">{{ $user->role->label() }}</span></dd>
            </div>
        </dl>
    </div>
</x-dashboard-layout>
@endsection
