@props(['title' => null, 'subtitle' => null, 'variant' => 'default'])

@php
    $baseClasses = 'rounded-xl border border-border bg-surface p-4 sm:p-6 shadow-xs';
    $variantClasses = match($variant) {
        'interactive' => 'transition-all duration-200 hover:border-border-strong hover:shadow-md hover:-translate-y-[3px]',
        'flat' => 'shadow-none',
        'muted' => 'bg-surface-muted shadow-none',
        default => '',
    };
    $classes = "{$baseClasses} {$variantClasses}";
@endphp

<section {{ $attributes->class($classes) }}>
    @if ($title || $subtitle || isset($actions))
        <header class="flex items-start justify-between gap-4 mb-5">
            <div>
                @if ($title)<h2 class="text-lg font-semibold text-text leading-tight">{{ $title }}</h2>@endif
                @if ($subtitle)<p class="text-text-muted text-sm mt-1">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    {{ $slot }}
</section>
