@props(['label', 'image', 'count' => null, 'tone' => 'primary', 'href' => '#'])

<a href="{{ $href }}" {{ $attributes->class(['nageeb-category', 'nageeb-category--'.$tone]) }}>
    <div class="nageeb-category__media nageeb-media nageeb-media--zoom">
        <x-nageeb-img :path="$image" :alt="$label" />
    </div>
    <div>
        <p class="nageeb-type-h4">{{ $label }}</p>
        @if ($count !== null)
            <p class="nageeb-type-caption mt-0.5">{{ $count }}</p>
        @endif
    </div>
</a>
