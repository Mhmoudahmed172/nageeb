@extends('layouts.app')

@section('title', $teacher->name.' — نجيب')

@section('content')
<x-public-layout>
    <x-reveal class="nageeb-teacher-hero mb-12">
        <div class="nageeb-teacher-hero__portrait nageeb-media">
            <img src="{{ \App\Support\NageebVisual::teacherPhoto($teacher) }}" alt="{{ $teacher->name }}" loading="lazy">
        </div>
        <div>
            <p class="nageeb-kicker">معلّم موثّق</p>
            <h1 class="nageeb-type-h1 mt-2">{{ $teacher->name }}</h1>
            <p class="nageeb-type-body-lg nageeb-text-muted mt-2">{{ $teacher->teacherProfile?->specialization ?? 'معلّم' }}</p>
            <p class="nageeb-type-body text-text-muted mt-4 max-w-xl">{{ $teacher->teacherProfile?->bio ?: 'لا توجد نبذة بعد.' }}</p>
            <p class="nageeb-type-caption mt-5">{{ $courses->count() }} مواد منشورة</p>
        </div>
    </x-reveal>

    <x-section-header kicker="المواد" title="المواد المتاحة" />
    @if ($courses->isEmpty())
        <x-empty-state title="لا توجد مواد منشورة حالياً." />
    @else
        <x-reveal stagger class="grid gap-5 sm:grid-cols-2">
            @foreach ($courses as $course)
                <div class="nageeb-reveal-item">
                    <x-course-card
                        variant="marketplace"
                        :title="$course->title"
                        :teacher="$teacher->name"
                        :image="\App\Support\NageebVisual::courseCover($course, $loop->index)"
                        :subject="\App\Support\NageebVisual::subjectLabel($course->title)"
                        :grade="$course->grade_level?->label()"
                        :href="route('courses.subscribe', $course)"
                        cta="عرض المادة"
                    />
                </div>
            @endforeach
        </x-reveal>
    @endif
</x-public-layout>
@endsection
