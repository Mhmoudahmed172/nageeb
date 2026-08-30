@props([
    'kicker' => 'أكمل التعلّم',
    'title',
    'subtitle' => null,
    'image' => null,
    'href' => '#',
    'cta' => 'متابعة التعلّم',
    'progress' => null,
])

<article {{ $attributes->class('nageeb-continue-hero') }}>
    <div class="nageeb-continue-hero__media nageeb-media">
        @if ($image)
            <img src="{{ $image }}" alt="" loading="lazy" decoding="async">
        @else
            <x-nageeb-img path="courses/dashboard-cover.png" alt="" />
        @endif
    </div>
    <div class="nageeb-continue-hero__body">
        <p class="nageeb-kicker">{{ $kicker }}</p>
        <h3 class="nageeb-type-h2">{{ $title }}</h3>
        @if ($subtitle)
            <p class="nageeb-type-body nageeb-text-muted">{{ $subtitle }}</p>
        @endif
        @if ($progress !== null)
            <x-progress :value="$progress" label="مكتمل" />
        @endif
        <div class="mt-2">
            <x-button :href="$href" size="lg">{{ $cta }}</x-button>
        </div>
    </div>
</article>
