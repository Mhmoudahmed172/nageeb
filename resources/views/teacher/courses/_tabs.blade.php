@props(['course', 'active' => 'packages'])

<nav class="nageeb-tabs mb-8 border-b border-border pb-3" aria-label="تبويبات المادة">
    <a
        href="{{ route('teacher.courses.edit', $course) }}"
        @class([
            'px-4 py-2 text-sm font-medium',
            'bg-primary text-text-inverse' => $active === 'details',
            'text-text hover:bg-primary-muted' => $active !== 'details',
        ])
    >
        تفاصيل المادة
    </a>
    <a
        href="{{ route('teacher.courses.content', $course) }}"
        @class([
            'px-4 py-2 text-sm font-medium',
            'bg-primary text-text-inverse' => $active === 'content',
            'text-text hover:bg-primary-muted' => $active !== 'content',
        ])
        @if ($active === 'content') aria-current="page" @endif
    >
        الوحدات والدروس
    </a>
    <a
        href="{{ route('teacher.courses.packages.index', $course) }}"
        @class([
            'px-4 py-2 text-sm font-medium',
            'bg-primary text-text-inverse' => $active === 'packages',
            'text-text hover:bg-primary-muted' => $active !== 'packages',
        ])
        @if ($active === 'packages') aria-current="page" @endif
    >
        خطط الوصول
    </a>
</nav>
