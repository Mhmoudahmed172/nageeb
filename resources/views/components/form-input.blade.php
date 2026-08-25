@props(['label', 'name', 'type' => 'text', 'required' => false, 'value' => null])

<div class="nageeb-field">
    <label for="{{ $name }}" class="nageeb-label">
        {{ $label }}
        @if ($required)
            <span class="text-alert">*</span>
        @endif
    </label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'nageeb-input']) }}
    />
    @error($name)
        <p class="nageeb-field-error">{{ $message }}</p>
    @enderror
</div>
