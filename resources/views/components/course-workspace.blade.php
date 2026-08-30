@props(['course', 'active' => 'content'])

@php
    $tabs = [
        ['key' => 'overview', 'label' => 'نظرة عامة', 'route' => 'teacher.courses.overview'],
        ['key' => 'content', 'label' => 'المحتوى', 'route' => 'teacher.courses.content'],
        ['key' => 'packages', 'label' => 'خطط الوصول والأسعار', 'route' => 'teacher.courses.packages.index'],
        ['key' => 'students', 'label' => 'الطلاب', 'route' => 'teacher.courses.students'],
        ['key' => 'analytics', 'label' => 'التحليلات', 'route' => 'teacher.courses.analytics'],
        ['key' => 'settings', 'label' => 'الإعدادات', 'route' => 'teacher.courses.settings'],
    ];
@endphp

<x-dashboard-layout title="{{ $course->title }}" role-label="المعلّم" active-menu="courses">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <header class="course-workspace-header mb-6 p-4 sm:p-5 bg-surface border border-border rounded-xl">
        <div class="course-workspace-header__cover rounded-lg nageeb-media">
            <img src="{{ \App\Support\NageebVisual::courseCover($course) }}" alt="" class="course-workspace-header__img" loading="lazy">
        </div>
        <div class="course-workspace-header__meta">
            <h1 class="nageeb-heading-2 mb-2">{{ $course->title }}</h1>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="nageeb-text-muted text-sm">{{ $course->grade_level?->label() ?? '—' }}</span>
                <x-badge variant="{{ $course->status === \App\Enums\CourseStatus::Live ? 'success' : 'warning' }}">{{ $course->status->label() }}</x-badge>
            </div>
        </div>
        <div class="course-workspace-header__actions">
            <x-button href="{{ route('teacher.courses.preview', $course) }}" variant="outline" size="sm">معاينة</x-button>
            <x-button href="{{ route('teacher.courses.edit', $course) }}" variant="secondary" size="sm">تعديل المادة</x-button>
            <x-button href="{{ route('teacher.courses.settings', $course) }}" variant="ghost" size="sm">إعدادات</x-button>
            <form method="POST" action="{{ route('teacher.courses.publish', $course) }}">
                @csrf
                <x-button type="submit" size="sm">
                    {{ $course->status === \App\Enums\CourseStatus::Live ? 'إلغاء النشر' : 'نشر' }}
                </x-button>
            </form>
        </div>
    </header>

    <nav class="nageeb-tabs mb-8" aria-label="تبويبات إدارة المادة">
        @foreach ($tabs as $tab)
            <a
                href="{{ route($tab['route'], $course) }}"
                @class([
                    'nageeb-tab',
                    'nageeb-tab--active' => $active === $tab['key'],
                ])
                @if ($active === $tab['key']) aria-current="page" @endif
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>

    {{ $slot }}
</x-dashboard-layout>
