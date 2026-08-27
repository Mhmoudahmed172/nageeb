@props(['height' => '1rem', 'width' => '100%'])

<span
    aria-hidden="true"
    {{ $attributes->class('nageeb-skeleton')->style("display:block;height:{$height};width:{$width}") }}
></span>
