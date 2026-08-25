@props(['label', 'name', 'type' => 'text', 'required' => false, 'value' => null, 'id' => null])

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
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'nageeb-input']) }}
    />
    @error($name)
        <p class="nageeb-field-error" role="alert">{{ $message }}</p>
    @enderror
</div>
