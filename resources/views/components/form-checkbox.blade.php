@props(['label', 'name', 'value' => 1, 'checked' => false, 'help' => null])

<label class="nageeb-check">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $attributes }}
    >
    <span>
        <span class="font-medium">{{ $label }}</span>
        @if ($help)<span class="nageeb-field-help block mt-1">{{ $help }}</span>@endif
    </span>
</label>
