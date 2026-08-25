@extends('layouts.app')

@section('title', $course->title.' — نجيب')

@section('content')
<x-dashboard-layout title="{{ $course->title }}" role-label="الطالب" active-menu="courses">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    @if ($currentLesson)
        <div class="nageeb-card mb-8">
            <h2 class="font-semibold mb-4">{{ $currentLesson->title }}</h2>
            @if ($currentLesson->content_type === \App\Enums\LessonContentType::UploadedVideo && $currentLesson->videoUrl())
                <video class="w-full bg-text" controls src="{{ $currentLesson->videoUrl() }}"></video>
            @elseif ($currentLesson->content_type === \App\Enums\LessonContentType::ExternalLink && $currentLesson->embedUrl())
                <iframe class="w-full aspect-video" src="{{ $currentLesson->embedUrl() }}" allowfullscreen title="{{ $currentLesson->title }}"></iframe>
            @else
                <p class="nageeb-text-muted">لا يوجد مصدر فيديو لهذا الدرس بعد.</p>
            @endif

            <div class="flex flex-wrap gap-3 mt-4">
                @if ($previousLesson)
                    <a href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $previousLesson->id]) }}" class="nageeb-btn nageeb-btn--outline">الدرس السابق</a>
                @endif
                @if ($nextLesson)
                    <a href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $nextLesson->id]) }}" class="nageeb-btn nageeb-btn--primary">الدرس التالي</a>
                @endif
            </div>

            @if ($currentLesson->attachments->isNotEmpty())
                <div class="mt-6">
                    <h3 class="font-medium mb-3">مرفقات الدرس</h3>
                    <ul class="grid gap-2">
                        @foreach ($currentLesson->attachments as $attachment)
                            <li>
                                <a href="{{ $attachment->url() }}" download>{{ $attachment->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="nageeb-card mb-8">
            <h2 class="nageeb-title-section mb-4">الأسئلة والتعليقات</h2>
            <form method="POST" action="{{ route('student.my-courses.comments.store', [$course, $currentLesson]) }}" class="grid gap-3 mb-8">
                @csrf
                <x-form-textarea label="سؤال جديد" name="message" required rows="3" />
                <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">إرسال السؤال</button>
            </form>

            @forelse ($questions as $question)
                <article class="border-b border-border py-4 last:border-0">
                    <p class="font-medium mb-1">{{ $question->user->name }}</p>
                    <p class="mb-3">{{ $question->message }}</p>
                    @foreach ($question->replies as $reply)
                        <div class="me-6 mb-2 p-3 bg-primary-muted">
                            <p class="text-sm font-medium">{{ $reply->user->name }}</p>
                            <p class="text-sm">{{ $reply->message }}</p>
                        </div>
                    @endforeach
                </article>
            @empty
                <x-empty-state title="لا توجد أسئلة على هذا الدرس بعد." />
            @endforelse
        </div>
    @else
        <div class="nageeb-alert nageeb-alert--info mb-8">لم يُضف محتوى لهذه المادة بعد.</div>
    @endif

    <div class="nageeb-card" x-data>
        <h2 class="nageeb-title-section mb-4">محتوى المادة</h2>
        @forelse ($course->units as $unit)
            <details class="border-b border-border py-3" open>
                <summary class="font-medium cursor-pointer">{{ $unit->title }}</summary>
                <ul class="mt-3 grid gap-1">
                    @foreach ($unit->lessons as $lesson)
                        <li>
                            <a
                                href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $lesson->id]) }}"
                                @class(['block py-2 px-3', 'bg-primary text-text-inverse' => $currentLesson?->id === $lesson->id])
                            >
                                {{ $lesson->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </details>
        @empty
            <x-empty-state title="لا توجد وحدات بعد." />
        @endforelse
    </div>
</x-dashboard-layout>
@endsection
