@props(['label', 'name', 'required' => false, 'help' => null, 'id' => null])

@php($fieldId = $id ?? $name)

<div class="nageeb-field">
    <label for="{{ $fieldId }}" class="nageeb-label">
        {{ $label }}
        @if ($required)
            <span class="text-alert">*</span>
        @endif
    </label>
    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'nageeb-input nageeb-select']) }}
    >
        {{ $slot }}
    </select>
    @if ($help)<p class="nageeb-field-help">{{ $help }}</p>@endif
    @error($name)
        <p class="nageeb-field-error" role="alert">{{ $message }}</p>
    @enderror
</div>
