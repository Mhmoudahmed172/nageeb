@props(['title' => null, 'subtitle' => null, 'variant' => 'default'])

<section {{ $attributes->class([
    'nageeb-card',
    'nageeb-card--muted' => $variant === 'muted',
    'nageeb-card--interactive' => $variant === 'interactive',
    'nageeb-card--flat' => $variant === 'flat',
]) }}>
    @if ($title || $subtitle || isset($actions))
        <header class="flex items-start justify-between gap-4 mb-5">
            <div>
                @if ($title)<h2 class="nageeb-heading-3">{{ $title }}</h2>@endif
                @if ($subtitle)<p class="nageeb-text-muted text-sm mt-1">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
        </header>
    @endif
    {{ $slot }}
</section>
