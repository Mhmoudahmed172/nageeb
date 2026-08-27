@extends('layouts.app')

@section('title', 'نجيب — تعلّم من معلّمين فلسطينيين')

@section('content')
<header class="bg-primary text-text-inverse">
    <div class="nageeb-container py-5 flex items-center justify-between gap-4 flex-wrap">
        <a href="{{ url('/') }}" class="text-text-inverse hover:text-text-inverse hover:opacity-90">
            <span class="text-xl font-bold">نجيب</span>
        </a>
        <nav class="flex items-center gap-3 sm:gap-5 text-sm flex-wrap">
            <a href="{{ route('courses.index') }}" class="text-text-inverse hover:text-text-inverse hover:opacity-80">المواد</a>
            @auth
                <a href="{{ auth()->user()->dashboardRoute() }}" class="text-text-inverse hover:text-text-inverse hover:opacity-80">لوحتي</a>
            @else
                <a href="{{ route('login') }}" class="text-text-inverse hover:text-text-inverse hover:opacity-80">دخول</a>
                <a href="{{ route('register.student') }}" class="nageeb-btn nageeb-btn--secondary py-2 px-4 text-sm">ابدأ التعلّم</a>
            @endauth
        </nav>
    </div>
</header>

<section class="bg-primary text-text-inverse pb-16 pt-8 md:pb-24 md:pt-12">
    <div class="nageeb-container grid gap-12 lg:grid-cols-12 lg:items-end">
        <div class="lg:col-span-5">
            <p class="text-sm opacity-80 mb-4 border-s-4 border-secondary ps-3">منصة دروس مباشرة ومسجّلة — غزة والضفة والخارج</p>
            <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">نجيب: المادة من معلّمها، والسعر حسب منطقتك.</h1>
            <p class="text-base md:text-lg opacity-90 mb-8 max-w-md leading-relaxed">
                اشتراك بإيصال دفع يراجعه المعلّم. لا قوالب جاهزة — هذه أرقام المنصة اليوم:
            </p>
            <dl class="flex flex-wrap gap-8 mb-10">
                <div>
                    <dt class="text-sm opacity-70">طلاب مسجّلون</dt>
                    <dd class="text-3xl md:text-4xl font-bold price">{{ $studentsCount }}</dd>
                </div>
                <div>
                    <dt class="text-sm opacity-70">معلّمون</dt>
                    <dd class="text-3xl md:text-4xl font-bold price">{{ $teachersCount }}</dd>
                </div>
                <div>
                    <dt class="text-sm opacity-70">مواد حيّة</dt>
                    <dd class="text-3xl md:text-4xl font-bold price">{{ $liveCoursesCount }}</dd>
                </div>
            </dl>
            @guest
                <a href="{{ route('register.student') }}" class="nageeb-btn nageeb-btn--secondary">ابدأ التعلّم</a>
            @else
                <a href="{{ auth()->user()->dashboardRoute() }}" class="nageeb-btn nageeb-btn--secondary">إلى لوحتي</a>
            @endguest
        </div>

        <div class="lg:col-span-7">
            @if ($heroCourses->isEmpty())
                <div class="bg-surface text-text p-8 md:p-12">
                    <p class="text-sm nageeb-text-dim mb-2">المواد الحيّة</p>
                    <p class="text-xl font-semibold">لم تُنشر مواد بعد. تصفّح الكتالوج لاحقاً أو سجّل كمعلّم وأضف مقررك.</p>
                    <a href="{{ route('courses.index') }}" class="inline-block mt-6 text-primary font-medium">عرض كل المواد</a>
                </div>
            @else
                @php $featured = $heroCourses->first(); $rest = $heroCourses->skip(1); @endphp
                <article class="bg-surface text-text p-6 md:p-10 mb-3">
                    <p class="text-xs font-medium text-primary mb-3">المادة الأحدث المنشورة</p>
                    <h2 class="text-2xl md:text-4xl font-bold leading-tight mb-3">{{ $featured->title }}</h2>
                    <p class="nageeb-text-muted mb-6">{{ $featured->teacher->name }}@if($featured->grade_level) · {{ $featured->grade_level }}@endif</p>
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <p class="text-sm">
                            <span class="price text-2xl font-bold text-primary">{{ $featured->enrollments_count }}</span>
                            <span class="nageeb-text-dim"> ملتحقاً حتى الآن</span>
                        </p>
                        <a href="{{ route('courses.subscribe', $featured) }}" class="text-sm font-medium">عرض المادة ←</a>
                    </div>
                </article>
                @foreach ($rest as $course)
                    <a href="{{ route('courses.subscribe', $course) }}" class="flex items-baseline justify-between gap-4 bg-primary-muted text-text px-5 py-4 mb-2 last:mb-0 hover:bg-surface">
                        <span class="font-medium truncate">{{ $course->title }}</span>
                        <span class="text-sm nageeb-text-muted shrink-0">{{ $course->teacher->name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</section>

@if ($teachers->isNotEmpty())
<section class="nageeb-container py-16 md:py-24">
    <div class="grid gap-10 md:grid-cols-12">
        <div class="md:col-span-4">
            <h2 class="text-3xl font-bold mb-3">المعلّمون الموثّقون</h2>
            <p class="nageeb-text-muted">من يدرّس فعلياً على نجيب، مرتّبون بعدد المواد الحيّة.</p>
        </div>
        <ol class="md:col-span-8 divide-y divide-border">
            @foreach ($teachers as $teacher)
                <li class="py-5 flex flex-wrap items-baseline justify-between gap-3">
                    <div>
                        <a href="{{ route('teachers.show', $teacher) }}" class="text-lg font-semibold">{{ $teacher->name }}</a>
                        <p class="text-sm nageeb-text-muted">{{ $teacher->teacherProfile?->specialization ?: 'معلّم' }}</p>
                    </div>
                    <p class="text-sm">
                        <span class="price font-bold">{{ $teacher->live_courses_count }}</span>
                        <span class="nageeb-text-dim"> مادة حيّة</span>
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
@else
<section class="nageeb-container py-16">
    <x-empty-state title="لا يوجد معلّمون موثّقون بعد.">
        تظهر هنا أسماء من وثّقتهم الإدارة ولديهم ملف على المنصة.
    </x-empty-state>
</section>
@endif

<section class="bg-surface-muted py-16 md:py-24">
    <div class="nageeb-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <h2 class="text-3xl font-bold">استكشاف المواد</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium">كل المواد المنشورة</a>
        </div>
        @if ($exploreCourses->isEmpty() && $heroCourses->isEmpty())
            <x-empty-state title="لا توجد مواد منشورة للعرض بعد." action-href="{{ route('courses.index') }}" action-label="فتح الكتالوج" />
        @elseif ($exploreCourses->isEmpty())
            <p class="nageeb-text-muted">المواد الحيّة الثلاث الأحدث ظاهرة في الأعلى. <a href="{{ route('courses.index') }}">عرض الكتالوج كاملاً</a></p>
        @else
            <ul class="grid gap-px bg-border">
                @foreach ($exploreCourses as $course)
                    <li class="bg-background">
                        <a href="{{ route('courses.subscribe', $course) }}" class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2 px-5 py-6">
                            <span class="text-lg font-medium">{{ $course->title }}</span>
                            <span class="text-sm nageeb-text-muted">{{ $course->teacher->name }}@if($course->grade_level) · {{ $course->grade_level }}@endif</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

<footer class="nageeb-container py-10 flex flex-wrap justify-between gap-4 text-sm nageeb-text-dim">
    <span>نجيب</span>
    <span>منصة تعليمية احترافية</span>
</footer>
@endsection
