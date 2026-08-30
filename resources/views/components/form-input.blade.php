@props(['label', 'name', 'type' => 'text', 'required' => false, 'value' => null, 'id' => null, 'help' => null])

@php
    $fieldId = $id ?? $name;
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $fieldId }}" class="text-sm font-medium text-text">
        {{ $label }}
        @if ($required)
            <span class="text-danger ml-0.5">*</span>
        @endif
    </label>
    <input
        type="{{ $type }}"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @if($help) aria-describedby="{{ $fieldId }}-help" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border-strong bg-surface px-3.5 py-2.5 text-base text-text transition-all duration-150 placeholder:text-text-dim hover:border-text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 aria-[invalid=true]:border-danger aria-[invalid=true]:focus:ring-danger/20']) }}
    />
    @if ($help)<p id="{{ $fieldId }}-help" class="text-xs text-text-dim mt-0.5">{{ $help }}</p>@endif
    @error($name)
        <p class="text-sm text-danger mt-0.5" role="alert">{{ $message }}</p>
    @enderror
</div>
