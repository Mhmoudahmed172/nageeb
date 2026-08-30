@props(['label', 'image', 'count' => null, 'href' => '#', 'icon' => 'book'])

<a href="{{ $href }}" {{ $attributes->class('nageeb-subject') }}>
    <span class="nageeb-subject__media">
        <x-nageeb-img :path="$image" :alt="$label" />
    </span>
    <span class="nageeb-subject__body">
        <svg class="nageeb-subject__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            @switch($icon)
                @case('math')
                    <path d="M5 8h6M8 5v6M15 7l4 4M19 7l-4 4M13 16h6M13 20h6" stroke-width="1.7" stroke-linecap="round"/>
                    @break
                @case('physics')
                    <circle cx="12" cy="12" r="2.2" stroke-width="1.7"/>
                    <ellipse cx="12" cy="12" rx="9" ry="3.6" stroke-width="1.5"/>
                    <ellipse cx="12" cy="12" rx="9" ry="3.6" transform="rotate(60 12 12)" stroke-width="1.5"/>
                    <ellipse cx="12" cy="12" rx="9" ry="3.6" transform="rotate(-60 12 12)" stroke-width="1.5"/>
                    @break
                @case('chemistry')
                    <path d="M9 3h6M10 3v5.2L6.4 16.5A4 4 0 0 0 10 22h4a4 4 0 0 0 3.6-5.5L14 8.2V3" stroke-width="1.7" stroke-linejoin="round"/>
                    @break
                @case('english')
                    <path d="M4 5h7v14H4zM13 5h7v14h-7z" stroke-width="1.7"/>
                    <path d="M7.5 8h1M7.5 12h2" stroke-width="1.7" stroke-linecap="round"/>
                    @break
                @case('computer')
                    <rect x="3" y="5" width="18" height="12" rx="2" stroke-width="1.7"/>
                    <path d="M8 21h8M12 17v4" stroke-width="1.7" stroke-linecap="round"/>
                    @break
                @default
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14Z" stroke-width="1.7"/>
            @endswitch
        </svg>
        <span class="font-bold text-lg leading-tight">{{ $label }}</span>
        @if ($count !== null && $count !== '')
            <span class="text-sm text-white/80">{{ $count }}</span>
        @endif
    </span>
</a>
