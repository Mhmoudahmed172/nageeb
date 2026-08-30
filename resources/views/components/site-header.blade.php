@props(['current' => null])

<header
    class="nageeb-site-header"
    x-data="siteNav"
    x-bind:class="scrolled && 'is-scrolled'"
    @keydown.escape.window="open = false"
>
    <div class="nageeb-container nageeb-site-header__bar">
        <a href="{{ url('/') }}" class="nageeb-site-header__brand nageeb-public-nav__brand">
            <span class="nageeb-mark">ن</span>
            <span>نجيب</span>
        </a>
        <nav class="nageeb-site-header__links" aria-label="التنقّل الرئيسي">
            <a href="{{ url('/') }}" @if ($current === 'home') aria-current="page" @endif>الرئيسية</a>
            <a href="{{ route('courses.index') }}" @if ($current === 'courses') aria-current="page" @endif>المواد التعليمية</a>
            <a href="{{ url('/#teachers') }}">المعلمون</a>
            @auth
                @if (auth()->user()->isStudent())
                    <a href="{{ route('student.exams.index') }}">الاختبارات</a>
                @endif
                <a href="{{ auth()->user()->dashboardRoute() }}">لوحتي</a>
            @endauth
        </nav>
        <div class="nageeb-site-header__actions">
            @guest
                <x-button variant="ghost" size="sm" href="{{ route('login') }}" class="hidden sm:inline-flex">دخول</x-button>
                <x-button variant="primary" size="sm" href="{{ route('register.student') }}">ابدأ رحلتك</x-button>
            @else
                <x-button variant="primary" size="sm" href="{{ auth()->user()->dashboardRoute() }}">إلى لوحتي</x-button>
            @endguest
            <button
                type="button"
                class="nageeb-site-header__menu md:hidden"
                @click="open = !open"
                :aria-expanded="open"
                aria-controls="nageeb-mobile-nav"
                aria-label="القائمة"
            >
                <span aria-hidden="true" x-text="open ? '×' : '☰'"></span>
            </button>
        </div>
    </div>
    <div
        id="nageeb-mobile-nav"
        class="nageeb-site-header__panel md:hidden"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
    >
        <nav class="nageeb-container py-3 grid gap-1" aria-label="تنقّل الجوال">
            <a href="{{ url('/') }}" @if ($current === 'home') aria-current="page" @endif>الرئيسية</a>
            <a href="{{ route('courses.index') }}" @if ($current === 'courses') aria-current="page" @endif>المواد التعليمية</a>
            <a href="{{ url('/#teachers') }}">المعلمون</a>
            @auth
                @if (auth()->user()->isStudent())
                    <a href="{{ route('student.exams.index') }}">الاختبارات</a>
                @endif
                <a href="{{ auth()->user()->dashboardRoute() }}">لوحتي</a>
            @else
                <a href="{{ route('login') }}">دخول</a>
                <a href="{{ route('register.student') }}">حساب طالب</a>
                <a href="{{ route('register.teacher') }}">حساب معلّم</a>
            @endauth
        </nav>
    </div>
</header>
