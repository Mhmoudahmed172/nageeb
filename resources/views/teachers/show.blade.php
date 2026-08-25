@extends('layouts.app')

@section('title', $teacher->name.' — نجيب')

@section('content')
<x-public-layout>
    <div class="nageeb-card max-w-3xl mb-8">
        <div class="flex items-start gap-4">
            @if ($teacher->teacherProfile?->avatarSrc())
                <img src="{{ $teacher->teacherProfile->avatarSrc() }}" alt="" class="w-24 h-24 object-cover">
            @endif
            <div>
                <h1 class="nageeb-title-section mb-1">{{ $teacher->name }}</h1>
                <p class="nageeb-text-muted mb-3">{{ $teacher->teacherProfile?->specialization ?? 'معلّم' }}</p>
                <p>{{ $teacher->teacherProfile?->bio ?: 'لا توجد نبذة بعد.' }}</p>
            </div>
        </div>
    </div>

    <h2 class="nageeb-title-section mb-4">المواد المتاحة</h2>
    @if ($courses->isEmpty())
        <x-empty-state title="لا توجد مواد منشورة حالياً." />
    @else
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($courses as $course)
                <a href="{{ route('courses.subscribe', $course) }}" class="nageeb-card block">
                    <h3 class="font-semibold mb-2">{{ $course->title }}</h3>
                    <p class="nageeb-text-muted text-sm">{{ $course->grade_level }}</p>
                </a>
            @endforeach
        </div>
    @endif
</x-public-layout>
@endsection
