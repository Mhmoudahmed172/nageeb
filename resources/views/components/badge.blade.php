@props(['variant' => 'primary'])

@php
    $baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-primary-muted text-primary',
        'success' => 'bg-success-muted text-success',
        'warning' => 'bg-warning-muted text-warning',
        'danger' => 'bg-danger-muted text-danger',
        'info' => 'bg-info-muted text-info',
        'secondary' => 'bg-surface-muted text-text-muted border border-border',
        default => 'bg-primary-muted text-primary',
    };
    
    $classes = "{$baseClasses} {$variantClasses}";
@endphp

<span {{ $attributes->class($classes) }}>
    {{ $slot }}
</span>
