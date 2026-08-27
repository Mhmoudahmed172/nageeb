@extends('layouts.app')

@section('title', 'معاينة — '.$course->title)

@section('content')
<x-dashboard-layout title="معاينة المادة" role-label="المعلّم" active-menu="courses">
    <a href="{{ route('teacher.courses.content', $course) }}" class="nageeb-btn nageeb-btn--outline mb-8">العودة إلى الإدارة</a>
    <h1 class="text-2xl font-bold mb-2">{{ $course->title }}</h1>
    <p class="nageeb-text-muted mb-8">{{ $course->grade_level?->label() }} · {{ $course->status->label() }}</p>
    @forelse ($course->units as $unit)
        <section class="mb-8">
            <h2 class="font-semibold mb-3">{{ $unit->title }}</h2>
            <ol class="grid gap-1 text-sm">
                @foreach ($unit->lessons as $lesson)
                    <li>{{ sprintf('%02d', $lesson->position) }} {{ $lesson->title }}</li>
                @endforeach
            </ol>
        </section>
    @empty
        <p class="nageeb-text-muted">لا محتوى للمعاينة بعد.</p>
    @endforelse
</x-dashboard-layout>
@endsection
