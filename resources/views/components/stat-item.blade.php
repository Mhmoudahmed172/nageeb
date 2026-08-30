@props(['label', 'value', 'count' => null])

<div {{ $attributes->class('nageeb-stat-item') }}>
    <p class="nageeb-stat-item__value tabular-nums" @if ($count !== null) x-data="countUp('{{ $count }}')" x-text="display" @endif>{{ $value }}</p>
    <p class="nageeb-stat-item__label">{{ $label }}</p>
</div>
