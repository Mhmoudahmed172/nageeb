@props(['title', 'subtitle' => null])

<div class="nageeb-page nageeb-auth min-h-screen">
    <aside class="nageeb-auth__visual" aria-hidden="true">
        <x-nageeb-img path="hero/hero-student-studying.png" alt="" eager class="nageeb-auth__photo" />
        <div class="nageeb-auth__caption">
            <p class="nageeb-kicker">منصة تعليمية فلسطينية</p>
            <p class="nageeb-type-h3 mt-2 text-text-inverse">تعلّم بطريقة أوضح</p>
        </div>
    </aside>

    <div class="nageeb-auth__panel">
        <a href="{{ url('/') }}" class="nageeb-public-nav__brand mb-8 inline-flex">
            <span class="nageeb-mark">ن</span>
            <span>
                <span class="text-xl font-bold block">نجيب</span>
                <span class="nageeb-caption block">منصة تعليمية احترافية</span>
            </span>
        </a>

        <div class="w-full max-w-md">
            <div class="mb-8">
                <h2 class="nageeb-heading-1 mb-2">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="nageeb-text-muted">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="nageeb-card nageeb-card--elevated">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="text-center mt-6 text-sm nageeb-text-muted">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
