@props(['path', 'alt' => '', 'eager' => false])

<img
    src="{{ asset('images/nageeb/'.$path) }}"
    alt="{{ $alt }}"
    @if ($eager) fetchpriority="high" @else loading="lazy" decoding="async" @endif
    {{ $attributes }}
>
