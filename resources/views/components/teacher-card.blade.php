@props([
    'name',
    'subject',
    'photo',
    'students' => null,
    'courses' => null,
    'rating' => null,
    'bio' => null,
    'cta' => 'عرض الملف',
    'href' => '#',
    'variant' => 'default',
])

@php
    $photoSrc = str_starts_with((string) $photo, 'http') || str_starts_with((string) $photo, '/')
        ? $photo
        : asset('images/nageeb/'.$photo);
    $showcase = in_array($variant, ['editorial', 'portrait', 'showcase', 'featured'], true);
@endphp

<article {{ $attributes->class(['nageeb-teacher-card', 'nageeb-teacher-card--showcase' => $showcase, 'nageeb-teacher-card--featured' => $variant === 'featured']) }}>
    @if ($showcase)
        <div class="nageeb-teacher-card__orb" aria-hidden="true"></div>
        <div class="nageeb-teacher-card__frame">
            <img src="{{ $photoSrc }}" alt="{{ $name }}" loading="lazy" decoding="async">
        </div>
        @if ($subject)
            <p class="nageeb-teacher-card__badge">{{ $subject }}</p>
        @endif
        <h3 class="nageeb-teacher-card__name">{{ $name }}</h3>
        @if ($bio)
            <p class="nageeb-teacher-card__bio">{{ $bio }}</p>
        @endif
        <div class="nageeb-teacher-card__stats">
            @if ($courses !== null)
                <div class="nageeb-teacher-card__stat">
                    <strong class="tabular-nums">{{ $courses }}</strong>
                    <span>{{ (int) $courses === 1 ? 'مادة' : 'مواد' }}</span>
                </div>
            @endif
            @if ($students !== null)
                <div class="nageeb-teacher-card__stat">
                    <strong class="tabular-nums">{{ $students }}</strong>
                    <span>طالب</span>
                </div>
            @endif
            @if ($rating !== null)
                <div class="nageeb-teacher-card__stat">
                    <strong class="tabular-nums">{{ $rating }}</strong>
                    <span>تقييم</span>
                </div>
            @endif
        </div>
        <x-button variant="outline" size="sm" :href="$href" class="w-full">{{ $cta }}</x-button>
    @else
        <div class="nageeb-teacher-card__head">
            <img src="{{ $photoSrc }}" alt="{{ $name }}" class="nageeb-avatar nageeb-avatar--lg" loading="lazy" decoding="async">
            <div class="min-w-0">
                <h3 class="nageeb-type-h4 truncate">{{ $name }}</h3>
                <p class="nageeb-type-body-sm nageeb-text-muted mt-0.5">{{ $subject }}</p>
            </div>
        </div>
        @if ($bio)
            <p class="nageeb-type-body-sm text-text-muted leading-relaxed">{{ $bio }}</p>
        @endif
        <div class="nageeb-teacher-card__stats">
            @if ($students !== null)
                <div class="nageeb-teacher-card__stat">
                    <strong class="tabular-nums">{{ $students }}</strong>
                    <span>طالب</span>
                </div>
            @endif
            @if ($courses !== null)
                <div class="nageeb-teacher-card__stat">
                    <strong class="tabular-nums">{{ $courses }}</strong>
                    <span>مادة</span>
                </div>
            @endif
            @if ($rating !== null)
                <div class="nageeb-teacher-card__stat">
                    <strong class="tabular-nums">{{ $rating }}</strong>
                    <span>تقييم</span>
                </div>
            @endif
        </div>
        <x-button variant="outline" size="sm" :href="$href" class="w-full">{{ $cta }}</x-button>
    @endif
</article>
