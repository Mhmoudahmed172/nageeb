@props(['href' => '#', 'title', 'description' => null, 'time' => null])

<a href="{{ $href }}" {{ $attributes->class('nageeb-activity') }}>
    <span class="nageeb-activity__dot" aria-hidden="true"></span>
    <span class="min-w-0 flex-1">
        <span class="font-semibold text-sm block">{{ $title }}</span>
        @if ($description)
            <span class="nageeb-text-muted text-xs block truncate mt-0.5">{{ $description }}</span>
        @endif
    </span>
    @if ($time)
        <time class="nageeb-caption shrink-0">{{ $time }}</time>
    @endif
</a>
