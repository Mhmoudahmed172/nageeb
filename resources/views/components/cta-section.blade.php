@props(['title', 'lede' => null, 'actionHref' => null, 'actionLabel' => null])

<section {{ $attributes->class('nageeb-cta') }}>
    <x-nageeb-img path="illustrations/learning.png" alt="" class="nageeb-cta__art nageeb-cta__art--a" />
    <x-nageeb-img path="illustrations/achievement.png" alt="" class="nageeb-cta__art nageeb-cta__art--b" />
    <div class="relative max-w-xl">
        <h2 class="nageeb-type-h1">{{ $title }}</h2>
        @if ($lede)
            <p class="nageeb-type-body-lg nageeb-text-muted mt-3">{{ $lede }}</p>
        @endif
        @if ($actionHref && $actionLabel)
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <x-button variant="primary" size="lg" :href="$actionHref">{{ $actionLabel }}</x-button>
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
