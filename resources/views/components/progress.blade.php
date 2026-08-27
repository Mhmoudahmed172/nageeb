@props(['value' => 0, 'label' => null, 'showValue' => true])

@php $progress = max(0, min(100, (float) $value)); @endphp

<div {{ $attributes }}>
    @if ($label || $showValue)
        <div class="flex justify-between gap-3 text-sm mb-2">
            <span>{{ $label }}</span>
            @if ($showValue)<span class="tabular-nums nageeb-text-muted">{{ round($progress) }}%</span>@endif
        </div>
    @endif
    <div class="nageeb-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">
        <div class="nageeb-progress__bar" style="width: {{ $progress }}%"></div>
    </div>
</div>
