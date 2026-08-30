@props(['id', 'index', 'title', 'lede' => null])

<section id="{{ $id }}" {{ $attributes->class('nageeb-ds-section scroll-mt-28') }}>
    <header class="nageeb-section-intro">
        <p class="nageeb-section-intro__index">{{ $index }}</p>
        <h2 class="nageeb-type-h2">{{ $title }}</h2>
        @if ($lede)
            <p class="nageeb-type-body-lg">{{ $lede }}</p>
        @endif
    </header>
    {{ $slot }}
</section>
