@props(['name' => 'search', 'placeholder' => 'بحث…', 'value' => null])

<div class="relative">
    <svg class="absolute start-3 top-1/2 -translate-y-1/2 size-5 nageeb-text-dim pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <circle cx="11" cy="11" r="7" stroke-width="1.8"/><path d="m20 20-4-4" stroke-width="1.8"/>
    </svg>
    <input type="search" name="{{ $name }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" class="nageeb-input ps-10" {{ $attributes }}>
</div>
