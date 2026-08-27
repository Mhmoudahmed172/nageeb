@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'iconOnly' => false,
])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'nageeb-btn',
        'nageeb-btn--'.$variant,
        'nageeb-btn--sm' => $size === 'sm',
        'nageeb-btn--lg' => $size === 'lg',
        'nageeb-btn--icon' => $iconOnly,
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($loading)<span class="nageeb-spinner" aria-hidden="true"></span>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($loading) aria-busy="{{ $loading ? 'true' : 'false' }}" {{ $attributes->class($classes) }}>
        @if ($loading)<span class="nageeb-spinner" aria-hidden="true"></span>@endif
        {{ $slot }}
    </button>
@endif
