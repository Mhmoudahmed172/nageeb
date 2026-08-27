@props(['title', 'meta' => null, 'open' => false])

<details class="nageeb-unit" @if($open) open @endif>
    <summary class="nageeb-unit__summary">
        <span>{{ $title }}</span>
        <span class="flex items-center gap-3">
            @if ($meta)<span class="nageeb-caption">{{ $meta }}</span>@endif
            <svg class="size-4 transition-transform [[open]_&]:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m5.5 7.5 4.5 4 4.5-4"/></svg>
        </span>
    </summary>
    <div class="nageeb-unit__content grid gap-2">{{ $slot }}</div>
</details>
