@props(['delay' => 0, 'stagger' => false])

<div
    x-data="reveal({{ (int) $delay }})"
    x-bind:class="visible && 'is-visible'"
    {{ $attributes->class(['nageeb-reveal', 'nageeb-reveal-stagger' => $stagger]) }}
>
    {{ $slot }}
</div>
