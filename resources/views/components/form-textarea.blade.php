@props(['label', 'name', 'required' => false, 'value' => null, 'rows' => 4, 'id' => null])

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
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'nageeb-input']) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="nageeb-field-error" role="alert">{{ $message }}</p>
    @enderror
</div>
