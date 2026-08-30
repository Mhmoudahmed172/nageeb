@props(['title' => null, 'current' => null])

<div class="nageeb-page min-h-screen flex flex-col">
    <x-site-header :current="$current" />
    <main class="flex-1 py-12 sm:py-16">
        <div class="nageeb-container">
            @if ($title)
                <x-reveal>
                    <h1 class="nageeb-type-h1 mb-8">{{ $title }}</h1>
                </x-reveal>
            @endif
            {{ $slot }}
        </div>
    </main>
    <x-site-footer />
</div>
