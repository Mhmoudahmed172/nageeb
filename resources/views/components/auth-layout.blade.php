@props(['title', 'subtitle' => null])

<div class="min-h-screen flex flex-col">
    <header class="bg-primary text-text-inverse">
        <div class="nageeb-container py-5 flex items-center justify-between">
            <a href="{{ url('/') }}" class="hover:opacity-90 transition-opacity">
                <h1 class="text-xl font-bold">نجيب</h1>
                <p class="text-sm opacity-80 mt-0.5">منصة تعليمية إلكترونية</p>
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center py-10 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h2 class="nageeb-title-section mb-2">{{ $title }}</h2>
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
    </main>
</div>
