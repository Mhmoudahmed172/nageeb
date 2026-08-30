@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'iconOnly' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-sans font-medium leading-none rounded-lg border transition-all duration-200 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:ring-offset-2';
    
    $sizeClasses = match($size) {
        'sm' => 'min-h-[2.25rem] px-3 py-1.5 text-xs',
        'lg' => 'min-h-12 px-5 py-2.5 text-base',
        default => 'min-h-11 px-4 py-2 text-sm',
    };

    if ($iconOnly) {
        $sizeClasses = match($size) {
            'sm' => 'size-9 p-0 flex-none text-xs',
            'lg' => 'size-13 p-0 flex-none text-base',
            default => 'size-11 p-0 flex-none text-sm',
        };
    }

    $variantClasses = match($variant) {
        'primary' => 'border-transparent bg-primary text-white shadow-xs hover:bg-primary-hover hover:shadow-sm hover:-translate-y-px active:translate-y-0',
        'secondary' => 'border-border bg-surface-muted text-text hover:bg-border hover:-translate-y-px active:translate-y-0',
        'outline' => 'border-brown/35 bg-surface text-brown hover:bg-primary-muted hover:border-primary hover:shadow-sm hover:-translate-y-px active:translate-y-0',
        'ghost' => 'border-transparent bg-transparent text-text-muted hover:bg-surface-muted hover:text-text',
        'danger' => 'border-transparent bg-danger text-white shadow-xs hover:bg-danger-hover hover:-translate-y-px active:translate-y-0',
        default => 'border-transparent bg-primary text-white',
    };

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($loading) aria-busy="{{ $loading ? 'true' : 'false' }}" {{ $attributes->class($classes) }}>
        @if ($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        @endif
        {{ $slot }}
    </button>
@endif
