@props(['message' => session('status'), 'variant' => 'success'])

@if ($message)
    <div class="nageeb-toast-stack" aria-live="polite">
        <div
            x-data="{ visible: true }"
            x-init="setTimeout(() => visible = false, 5000)"
            x-show="visible"
            x-transition
            class="nageeb-toast"
        >
            <span class="size-2 rounded-full bg-{{ $variant }} mt-2 shrink-0" aria-hidden="true"></span>
            <p class="text-sm flex-1">{{ $message }}</p>
            <button type="button" class="nageeb-text-muted" @click="visible = false" aria-label="إغلاق">×</button>
        </div>
    </div>
@endif
