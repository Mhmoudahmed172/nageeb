@props([
    'title',
    'teacher' => null,
    'image' => null,
    'progress' => null,
    'href' => '#',
    'badge' => null,
    'variant' => 'default',
    'avatar' => null,
    'subject' => null,
    'grade' => null,
    'semester' => null,
    'lessons' => null,
    'students' => null,
    'price' => null,
    'region' => null,
    'cta' => null,
])

@php
    $variantClass = match ($variant) {
        'compact' => 'nageeb-course-card--compact',
        'featured' => 'nageeb-course-card--featured',
        'student' => 'nageeb-course-card--student',
        'teacher' => 'nageeb-course-card--teacher',
        'marketplace' => 'nageeb-course-card--marketplace',
        default => '',
    };

    $imageSrc = $image
        ? (str_starts_with((string) $image, 'http') || str_starts_with((string) $image, '/')
            ? $image
            : asset('images/nageeb/'.$image))
        : null;

    $avatarSrc = $avatar
        ? (str_starts_with((string) $avatar, 'http') || str_starts_with((string) $avatar, '/')
            ? $avatar
            : asset('images/nageeb/'.$avatar))
        : null;
@endphp

<article {{ $attributes->class(['nageeb-card nageeb-card--interactive nageeb-course-card', $variantClass]) }}>
    <a href="{{ $href }}" class="nageeb-course-card__link block h-full text-text hover:text-text">
        <div class="nageeb-course-card__media nageeb-media nageeb-media--zoom nageeb-media--overlay">
            @if ($imageSrc)
                <img src="{{ $imageSrc }}" alt="" loading="lazy" decoding="async">
            @else
                <div class="h-full grid place-items-center text-primary">
                    <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke-width="1.6"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20" stroke-width="1.6"/></svg>
                </div>
            @endif
            @if ($badge)<x-badge class="absolute top-3 start-3 z-10">{{ $badge }}</x-badge>@endif
            @if ($subject)<x-badge variant="success" class="absolute top-3 end-3 z-10">{{ $subject }}</x-badge>@endif
            @if ($cta)
                <span class="nageeb-course-card__overlay">
                    <span class="nageeb-btn nageeb-btn--primary nageeb-btn--sm">{{ $cta }}</span>
                </span>
            @endif
        </div>
        <div class="nageeb-course-card__body">
            @if ($grade || $semester)
                <p class="nageeb-type-caption mb-1">
                    {{ collect([$grade, $semester])->filter()->implode(' · ') }}
                </p>
            @endif
            <h3 class="nageeb-heading-3 line-clamp-2">{{ $title }}</h3>
            @if ($teacher)
                <div class="nageeb-course-card__teacher">
                    @if ($avatarSrc)
                        <img src="{{ $avatarSrc }}" alt="" class="nageeb-avatar nageeb-avatar--sm">
                    @endif
                    <span>{{ $teacher }}</span>
                </div>
            @endif
            @if ($lessons || $students || $region)
                <div class="nageeb-course-card__meta">
                    @if ($lessons)<span>{{ $lessons }}</span>@endif
                    @if ($students)<span>{{ $students }}</span>@endif
                    @if ($region)<span>{{ $region }}</span>@endif
                </div>
            @endif
            @if ($progress !== null)<x-progress :value="$progress" label="التقدم" class="mt-4" />@endif
            @if ($price)
                <p class="nageeb-course-card__price">{{ $price }}</p>
            @endif
        </div>
    </a>
</article>
