@props(['title' => null])

<div class="min-h-screen flex flex-col">
    <header class="bg-primary text-text-inverse">
        <div class="nageeb-container py-5 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="hover:opacity-90 transition-opacity">
                <span class="text-xl font-bold">نجيب</span>
            </a>
            <nav class="flex items-center gap-3 sm:gap-4 text-sm flex-wrap justify-end">
                <a href="{{ route('courses.index') }}" class="text-text-inverse hover:text-text-inverse hover:opacity-80">المواد</a>
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="text-text-inverse hover:text-text-inverse hover:opacity-80">لوحتي</a>
                @else
                    <a href="{{ route('login') }}" class="text-text-inverse hover:text-text-inverse hover:opacity-80">دخول</a>
                @endauth
            </nav>
        </div>
    </header>
    <main class="flex-1 py-10">
        <div class="nageeb-container">
            @if ($title)
                <h1 class="nageeb-title-section mb-8">{{ $title }}</h1>
            @endif
            {{ $slot }}
        </div>
    </main>
</div>
