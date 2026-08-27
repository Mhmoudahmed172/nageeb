@props(['label', 'name', 'checked' => false, 'help' => null])

<label class="flex items-start justify-between gap-4">
    <span>
        <span class="text-sm font-medium block">{{ $label }}</span>
        @if ($help)<span class="nageeb-field-help block mt-1">{{ $help }}</span>@endif
    </span>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" class="nageeb-switch shrink-0" @checked(old($name, $checked)) {{ $attributes }}>
</label>
