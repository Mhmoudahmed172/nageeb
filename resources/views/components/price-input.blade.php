@props(['label', 'name', 'value' => null, 'currency' => '₪', 'required' => false, 'id' => null])

@php($fieldId = $id ?? $name)

<div class="nageeb-field">
    <label class="nageeb-label" for="{{ $fieldId }}">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <div class="relative">
        <input id="{{ $fieldId }}" name="{{ $name }}" type="number" step="0.01" min="0" value="{{ old($name, $value) }}" class="nageeb-input ps-12" @required($required) {{ $attributes }}>
        <span class="absolute inset-y-0 start-0 flex items-center px-4 border-e border-border nageeb-text-muted" aria-hidden="true">{{ $currency }}</span>
    </div>
    @error($name)<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
</div>
