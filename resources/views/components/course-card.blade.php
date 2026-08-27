@props(['title', 'teacher' => null, 'image' => null, 'progress' => null, 'href' => '#', 'badge' => null])

<article {{ $attributes->class('nageeb-card nageeb-card--interactive nageeb-course-card') }}>
    <a href="{{ $href }}" class="block text-text hover:text-text">
        <div class="nageeb-course-card__media">
            @if ($image)
                <img src="{{ $image }}" alt="">
            @else
                <div class="h-full grid place-items-center text-primary">
                    <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke-width="1.6"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20" stroke-width="1.6"/></svg>
                </div>
            @endif
            @if ($badge)<x-badge class="absolute top-3 start-3">{{ $badge }}</x-badge>@endif
        </div>
        <div class="nageeb-course-card__body">
            <h3 class="nageeb-heading-3 line-clamp-2">{{ $title }}</h3>
            @if ($teacher)<p class="nageeb-text-muted text-sm mt-1">{{ $teacher }}</p>@endif
            @if ($progress !== null)<x-progress :value="$progress" label="التقدم" class="mt-4" />@endif
        </div>
    </a>
</article>
