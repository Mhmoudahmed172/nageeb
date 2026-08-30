@extends('layouts.app')

@section('title', $course->title.' — نجيب')

@section('content')
<x-dashboard-layout title="{{ $course->title }}" role-label="الطالب" active-menu="courses">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    @if ($currentLesson)
        <div class="nageeb-learn mb-8">
            <div>
        <div class="nageeb-lesson-player mb-6">
            <div class="nageeb-lesson-player__body !pb-0">
                <h2 class="nageeb-type-h3 mb-4">{{ $currentLesson->title }}</h2>
            </div>
            @if ($currentLesson->videoUrl())
                <div class="nageeb-lesson-player__stage">
                    <video class="w-full h-full object-contain bg-text" controls src="{{ $currentLesson->videoUrl() }}"></video>
                </div>
            @elseif ($currentLesson->embedUrl())
                <iframe class="w-full aspect-video" src="{{ $currentLesson->embedUrl() }}" allowfullscreen title="{{ $currentLesson->title }}"></iframe>
            @endif

            <div class="nageeb-lesson-player__body">
            @if ($currentLesson->contents->isNotEmpty())
                <div class="grid gap-3">
                    @foreach ($currentLesson->contents as $block)
                        @if ($block->type === \App\Enums\LessonContentType::File)
                            <a href="{{ $block->accessUrl() }}" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm">{{ $block->displayName() }}</a>
                        @elseif ($block->type === \App\Enums\LessonContentType::Link && ! empty($block->data['url']))
                            <a href="{{ $block->data['url'] }}" target="_blank" rel="noopener noreferrer" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm">{{ $block->displayName() }}</a>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap gap-3 mt-4">
                @if ($previousLesson)
                    <a href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $previousLesson->id]) }}" class="nageeb-btn nageeb-btn--outline">الدرس السابق</a>
                @endif
                @if ($nextLesson)
                    <a href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $nextLesson->id]) }}" class="nageeb-btn nageeb-btn--primary">الدرس التالي</a>
                @endif
            </div>

            @if (! empty($lessonExams) && count($lessonExams) > 0)
                <div class="mt-6">
                    <h3 class="font-medium mb-3">اختبارات الدرس</h3>
                    <ul class="grid gap-2">
                        @foreach ($lessonExams as $lessonExam)
                            <li class="flex flex-wrap items-center justify-between gap-2 border border-border rounded-md p-3">
                                <span class="text-sm font-medium">{{ $lessonExam->title }}</span>
                                <a href="{{ route('student.exams.show', $lessonExam) }}" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm">بدء الاختبار</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            </div>
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
            </div>

            <aside class="nageeb-learn__rail nageeb-card" x-data>
                <h2 class="nageeb-title-section mb-4">محتوى المادة</h2>
                <p class="nageeb-caption mb-3">{{ $course->title }}</p>
                @forelse ($course->units as $unit)
                    <details class="py-3" open>
                        <summary class="font-medium cursor-pointer">{{ $unit->title }}</summary>
                        <ul class="mt-3 grid gap-1">
                            @foreach ($unit->lessons as $lesson)
                                <li>
                                    <a
                                        href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $lesson->id]) }}"
                                        @class(['nageeb-rail-link', 'is-active' => $currentLesson?->id === $lesson->id])
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
            </aside>
        </div>
    @else
        <div class="nageeb-alert nageeb-alert--info mb-8">لم يُضف محتوى لهذه المادة بعد.</div>
        <div class="nageeb-card" x-data>
            <h2 class="nageeb-title-section mb-4">محتوى المادة</h2>
            @forelse ($course->units as $unit)
                <details class="py-3" open>
                    <summary class="font-medium cursor-pointer">{{ $unit->title }}</summary>
                    <ul class="mt-3 grid gap-1">
                        @foreach ($unit->lessons as $lesson)
                            <li>
                                <a href="{{ route('student.my-courses.show', ['course' => $course, 'lesson' => $lesson->id]) }}" class="nageeb-rail-link">
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
    @endif
</x-dashboard-layout>
@endsection
