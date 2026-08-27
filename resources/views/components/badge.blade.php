@props(['variant' => 'primary'])

<span {{ $attributes->class(['nageeb-badge', 'nageeb-badge--'.$variant]) }}>
    {{ $slot }}
</span>
