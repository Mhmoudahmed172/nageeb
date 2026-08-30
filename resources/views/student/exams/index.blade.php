@extends('layouts.app')

@section('title', 'اختباراتي — نجيب')

@section('content')
<x-dashboard-layout title="الاختبارات" role-label="الطالب" active-menu="exams">
    <h2 class="nageeb-heading-2 mb-1">الاختبارات المتاحة</h2>
    <p class="nageeb-text-muted text-sm mb-6">تظهر هنا اختبارات المواد التي لديك صلاحية الوصول إليها.</p>

    @if ($exams->isEmpty())
        <x-empty-state
            title="لا توجد اختبارات متاحة حاليًا."
            action-href="{{ route('student.my-courses.index') }}"
            action-label="تصفح موادي"
        />
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($exams as $exam)
                @php($examAttempts = $attempts->get($exam->id) ?? collect())
                @php($best = $examAttempts->whereNotNull('submitted_at')->sortByDesc('percentage')->first())
                <div class="nageeb-card nageeb-card--interactive overflow-hidden !p-0">
                    <div class="nageeb-media aspect-[16/7]">
                        <img src="{{ asset('images/nageeb/exams/exam-thumbnail.png') }}" alt="">
                    </div>
                    <div class="p-5">
                    <p class="nageeb-caption">{{ $exam->course->title }}{{ $exam->lesson ? ' · '.$exam->lesson->title : '' }}</p>
                    <h3 class="font-bold mt-1">{{ $exam->title }}</h3>
                    <p class="nageeb-caption mt-2">
                        {{ $exam->questions_count }} سؤال
                        @if ($exam->duration_minutes) · {{ $exam->duration_minutes }} دقيقة @endif
                        · {{ $examAttempts->count() }} / {{ $exam->max_attempts }} محاولة
                    </p>
                    @if ($best)
                        <p class="text-sm mt-2">
                            أفضل نتيجة: <span class="font-semibold">{{ (float) $best->percentage }}%</span>
                            <x-badge variant="{{ $best->passed ? 'success' : 'warning' }}">{{ $best->passed ? 'ناجح' : 'راسب' }}</x-badge>
                        </p>
                    @endif
                    <div class="mt-4">
                        <x-button href="{{ route('student.exams.show', $exam) }}" size="sm">فتح الاختبار</x-button>
                    </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-dashboard-layout>
@endsection
