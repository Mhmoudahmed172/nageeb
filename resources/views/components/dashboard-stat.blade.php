@props(['label', 'value', 'change' => null, 'icon' => null])

<div {{ $attributes->class('nageeb-kpi') }}>
    <div class="flex items-start justify-between gap-2">
        <p class="nageeb-type-caption">{{ $label }}</p>
        @if ($change && ($change['direction'] ?? 'flat') !== 'flat')
            <span @class([
                'text-xs font-mono',
                'text-success' => $change['direction'] === 'up',
                'text-danger' => $change['direction'] === 'down',
            ])>
                {{ $change['direction'] === 'up' ? '↑' : '↓' }} {{ $change['percent'] }}%
            </span>
        @endif
    </div>
    <strong class="tabular-nums">{{ $value }}</strong>
</div>
