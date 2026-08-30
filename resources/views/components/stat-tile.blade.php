@props(['label', 'value', 'hint' => null, 'tone' => 'primary', 'count' => null])

<div {{ $attributes->class(['nageeb-stat-tile', 'nageeb-stat-tile--'.$tone]) }}>
    <p class="nageeb-stat__label">{{ $label }}</p>
    <p class="nageeb-stat__value tabular-nums" @if ($count !== null) x-data="countUp('{{ $count }}')" x-text="display" @endif>
        {{ $value }}
    </p>
    @if ($hint)
        <p class="nageeb-type-caption mt-1">{{ $hint }}</p>
    @endif
    {{ $slot }}
</div>
