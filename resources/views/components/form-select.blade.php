@props(['label', 'name', 'required' => false])

<div class="nageeb-field">
    <label for="{{ $name }}" class="nageeb-label">
        {{ $label }}
        @if ($required)
            <span class="text-alert">*</span>
        @endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'nageeb-input nageeb-select']) }}
    >
        {{ $slot }}
    </select>
    @error($name)
        <p class="nageeb-field-error">{{ $message }}</p>
    @enderror
</div>
