@props(['kicker' => null, 'title', 'lede' => null])

<header {{ $attributes->class('nageeb-section-intro') }}>
    @if ($kicker)
        <p class="nageeb-kicker">{{ $kicker }}</p>
    @endif
    <h2 class="nageeb-type-h2 mt-2">{{ $title }}</h2>
    @if ($lede)
        <p>{{ $lede }}</p>
    @endif
    {{ $slot }}
</header>
