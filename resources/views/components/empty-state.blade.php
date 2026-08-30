@props(['title', 'actionHref' => null, 'actionLabel' => null, 'image' => null, 'plain' => false])

@php
    $resolvedImage = $plain ? null : ($image ?? \App\Support\NageebVisual::emptyImage((string) $title));
    $imageSrc = $resolvedImage
        ? (str_starts_with((string) $resolvedImage, 'http') || str_starts_with((string) $resolvedImage, '/')
            ? $resolvedImage
            : asset('images/nageeb/'.$resolvedImage))
        : null;
@endphp

<div {{ $attributes->class(['nageeb-empty', 'nageeb-empty--illustrated' => (bool) $imageSrc]) }}>
    @if ($imageSrc)
        <img src="{{ $imageSrc }}" alt="" class="nageeb-empty__image">
    @endif
    <p class="nageeb-empty__title">{{ $title }}</p>
    @if (trim((string) $slot) !== '')
        <div class="nageeb-empty__body">{{ $slot }}</div>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="nageeb-btn nageeb-btn--primary mt-4">{{ $actionLabel }}</a>
    @endif
</div>
