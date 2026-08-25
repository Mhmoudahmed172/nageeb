@props(['title', 'actionHref' => null, 'actionLabel' => null])

<div class="nageeb-empty">
    <p class="nageeb-empty__title">{{ $title }}</p>
    @if (trim((string) $slot) !== '')
        <div class="nageeb-empty__body">{{ $slot }}</div>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="nageeb-btn nageeb-btn--primary mt-4">{{ $actionLabel }}</a>
    @endif
</div>
