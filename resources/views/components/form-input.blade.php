@props(['label', 'name', 'type' => 'text', 'required' => false, 'value' => null, 'id' => null, 'help' => null])

@php
    $fieldId = $id ?? $name;
@endphp

<div class="nageeb-field">
    <label for="{{ $fieldId }}" class="nageeb-label">
        {{ $label }}
        @if ($required)
            <span class="text-alert">*</span>
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
        {{ $attributes->merge(['class' => 'nageeb-input']) }}
    />
    @if ($help)<p id="{{ $fieldId }}-help" class="nageeb-field-help">{{ $help }}</p>@endif
    @error($name)
        <p class="nageeb-field-error" role="alert">{{ $message }}</p>
    @enderror
</div>
