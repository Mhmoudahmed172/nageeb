@extends('layouts.app')

@section('title', 'مقرراتي — نجيب')

@section('content')
<x-dashboard-layout title="مقرراتي" role-label="المعلّم" active-menu="courses">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <p class="nageeb-text-muted">أدر موادك ثم أنشئ خطط الوصول والأسعار الخاصة بكل مادة.</p>
        <a href="{{ route('teacher.courses.create') }}" class="nageeb-btn nageeb-btn--primary self-start">إضافة مادة</a>
    </div>

    @if ($courses->isEmpty())
        <x-card>
            <x-empty-state title="لا توجد مواد بعد." action-href="{{ route('teacher.courses.create') }}" action-label="إنشاء مادة" />
        </x-card>
    @else
        <x-reveal stagger class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($courses as $course)
                <div class="nageeb-reveal-item">
                    <x-course-card
                        variant="teacher"
                        :title="$course->title"
                        :image="\App\Support\NageebVisual::courseCover($course, $loop->index)"
                        :grade="$course->grade_level?->label()"
                        :badge="$course->status->label()"
                        :href="route('teacher.courses.content', $course)"
                        cta="إدارة"
                    />
                    <div class="mt-2">
                        <a href="{{ route('teacher.courses.edit', $course) }}" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm">تعديل</a>
                    </div>
                </div>
            @endforeach
        </x-reveal>
    @endif
</x-dashboard-layout>
@endsection
