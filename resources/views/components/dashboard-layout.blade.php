@props(['title', 'roleLabel'])

<div class="min-h-screen flex flex-col">
    <header class="bg-primary text-text-inverse">
        <div class="nageeb-container py-5 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold">{{ $title }}</h1>
                <p class="text-sm opacity-80 mt-0.5">لوحة تحكم {{ $roleLabel }}</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm opacity-90 hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nageeb-btn nageeb-btn--secondary text-sm py-2 px-4">
                        خروج
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="nageeb-container py-10 flex-1">
        {{ $slot }}
    </main>
</div>
