@props(['label' => 'رفع ملف', 'name', 'accept' => null, 'multiple' => false, 'help' => null])

<div class="nageeb-field">
    <label class="nageeb-file" for="{{ $name }}">
        <span class="grid gap-1">
            <span class="font-semibold">{{ $label }}</span>
            <span class="nageeb-field-help">{{ $help ?? 'اسحب الملف هنا أو اضغط للاختيار' }}</span>
        </span>
    </label>
    <input id="{{ $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" type="file" class="sr-only" @if($accept) accept="{{ $accept }}" @endif @if($multiple) multiple @endif {{ $attributes }}>
    @error($name)<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
</div>
