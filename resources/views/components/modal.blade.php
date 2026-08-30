@props(['name', 'title', 'description' => null])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
>
    {{ $trigger ?? '' }}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="nageeb-modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $name }}-title"
        >
            <button type="button" class="absolute inset-0 cursor-default" aria-label="إغلاق" @click="open = false"></button>
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="nageeb-modal-panel relative"
                @click.stop
            >
                <header class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h2 id="{{ $name }}-title" class="nageeb-heading-2">{{ $title }}</h2>
                        @if ($description)<p class="nageeb-text-muted text-sm mt-1">{{ $description }}</p>@endif
                    </div>
                    <x-button variant="ghost" icon-only @click="open = false" aria-label="إغلاق">×</x-button>
                </header>
                {{ $slot }}
                @isset($footer)<footer class="flex flex-wrap justify-end gap-2 mt-6">{{ $footer }}</footer>@endisset
            </div>
        </div>
    </template>
</div>
