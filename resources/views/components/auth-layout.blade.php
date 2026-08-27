@props(['title', 'subtitle' => null])

<div class="min-h-screen flex flex-col">
    <header class="bg-surface border-b border-border">
        <div class="nageeb-container py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 text-text hover:text-primary">
                <span class="grid size-9 place-items-center rounded-md bg-primary text-white font-bold">ن</span>
                <span>
                    <span class="text-xl font-bold block">نجيب</span>
                    <span class="nageeb-caption block">منصة تعليمية احترافية</span>
                </span>
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center py-10 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
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
    </main>
</div>
