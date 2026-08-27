@props(['title', 'roleLabel', 'activeMenu' => ''])

@php
    $user = auth()->user();
    $unreadNotificationsCount = $user->unreadNotifications()->count();
    $latestNotifications = $user->notifications()->latest()->take(5)->get();

    $menuHref = static function (string $name): string {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
    };

    $menu = match ($user->role) {
        \App\Enums\UserRole::Teacher => [
            ['key' => 'dashboard', 'label' => 'الرئيسية', 'route' => 'teacher.dashboard', 'icon' => 'home'],
            [
                'label' => 'التعليم',
                'children' => [
                    ['key' => 'courses', 'label' => 'المواد', 'route' => 'teacher.courses.index', 'icon' => 'book'],
                    ['key' => 'content', 'label' => 'الوحدات والدروس', 'route' => 'teacher.courses.index', 'icon' => 'layers'],
                    ['key' => 'quizzes', 'label' => 'الاختبارات', 'route' => null, 'icon' => 'quiz'],
                    ['key' => 'assignments', 'label' => 'الواجبات', 'route' => null, 'icon' => 'assignment'],
                    ['key' => 'live', 'label' => 'البث المباشر', 'route' => null, 'icon' => 'live'],
                ],
            ],
            [
                'label' => 'الطلاب',
                'children' => [
                    ['key' => 'enrollments', 'label' => 'الطلاب', 'route' => 'teacher.enrollments.index', 'icon' => 'users'],
                    ['key' => 'engagement', 'label' => 'الأسئلة', 'route' => 'teacher.interactions.index', 'icon' => 'quiz'],
                    ['key' => 'messages', 'label' => 'الرسائل', 'route' => null, 'icon' => 'message'],
                ],
            ],
            [
                'label' => 'المبيعات',
                'children' => [
                    ['key' => 'packages', 'label' => 'الاشتراكات', 'route' => 'teacher.packages.index', 'icon' => 'subscription'],
                    ['key' => 'subscription-requests', 'label' => 'الطلبات', 'route' => 'teacher.subscription-requests.index', 'icon' => 'orders'],
                    ['key' => 'earnings', 'label' => 'الأرباح', 'route' => 'teacher.earnings.index', 'icon' => 'revenue'],
                ],
            ],
            ['key' => 'analytics', 'label' => 'التحليلات', 'route' => 'teacher.dashboard', 'icon' => 'analytics'],
            [
                'label' => 'الإعدادات',
                'children' => [
                    ['key' => 'profile', 'label' => 'الملف العام', 'route' => 'teacher.profile.edit', 'icon' => 'users'],
                    ['key' => 'settings', 'label' => 'إعدادات الحساب', 'route' => 'teacher.settings.edit', 'icon' => 'settings'],
                ],
            ],
        ],
        \App\Enums\UserRole::Student => [
            ['key' => 'dashboard', 'label' => 'لوحتي', 'route' => 'student.dashboard'],
            ['key' => 'courses', 'label' => 'موادي', 'route' => 'student.my-courses.index'],
            ['key' => 'settings', 'label' => 'إعدادات الحساب', 'route' => 'student.settings.edit'],
        ],
        \App\Enums\UserRole::Admin => [
            ['key' => 'dashboard', 'label' => 'نظرة عامة', 'route' => 'admin.dashboard'],
            ['key' => 'teachers', 'label' => 'المعلمون', 'route' => 'admin.teachers.index'],
            ['key' => 'payouts', 'label' => 'طلبات السحب', 'route' => 'admin.payouts.index'],
        ],
    };
@endphp

<div class="min-h-screen lg:flex" x-data="{ sidebarCollapsed: localStorage.getItem('nageeb-sidebar') === 'collapsed' }">
    <input
        type="checkbox"
        id="dashboard-sidebar-toggle"
        class="peer sr-only"
        autocomplete="off"
    >

    <label
        for="dashboard-sidebar-toggle"
        class="fixed inset-0 z-40 bg-text/45 backdrop-blur-sm hidden max-lg:peer-checked:block"
        aria-hidden="true"
    ></label>

    <aside
        class="fixed top-0 start-0 z-50 flex h-dvh w-[min(var(--nageeb-sidebar-width),90vw)] shrink-0 translate-x-full flex-col overflow-y-auto bg-surface border-e border-border shadow-lg transition-[width,transform] duration-200 max-lg:peer-checked:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0 lg:shadow-none"
        :class="sidebarCollapsed ? 'lg:w-[5.25rem]' : 'lg:w-[var(--nageeb-sidebar-width)]'"
    >
        <div class="px-5 py-5 flex items-start justify-between gap-3 border-b border-border">
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-2 text-text hover:text-primary">
                    <span class="grid size-9 place-items-center rounded-md bg-primary text-text-inverse font-bold">ن</span>
                    <span class="text-xl font-bold" x-show="!sidebarCollapsed">نجيب</span>
                </a>
                <p class="text-xs nageeb-text-muted mt-1" x-show="!sidebarCollapsed">مساحة {{ $roleLabel }}</p>
            </div>
            <label
                for="dashboard-sidebar-toggle"
                class="nageeb-btn nageeb-btn--ghost nageeb-btn--icon lg:hidden cursor-pointer"
                aria-label="إغلاق القائمة"
            >
                <span aria-hidden="true">&times;</span>
            </label>
        </div>

        <nav class="flex-1 px-3 py-5" aria-label="قائمة لوحة التحكم">
            <ul class="flex flex-col gap-4">
                @foreach ($menu as $item)
                    <li>
                        @if (! empty($item['children']))
                            <p class="nageeb-text-dim text-xs font-semibold px-3 mb-2" x-show="!sidebarCollapsed">{{ $item['label'] }}</p>
                            <ul class="flex flex-col gap-1">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a
                                            href="{{ $child['route'] ? $menuHref($child['route']) : '#' }}"
                                            @class([
                                                'flex w-full items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors',
                                                'bg-primary-muted text-primary' => $activeMenu === $child['key'],
                                                'text-text-muted hover:bg-surface-muted hover:text-text' => $activeMenu !== $child['key'] && $child['route'],
                                                'text-text-dim opacity-55 cursor-not-allowed' => ! $child['route'],
                                            ])
                                            @if ($activeMenu === $child['key']) aria-current="page" @endif
                                            @if (! $child['route']) aria-disabled="true" title="قريباً" @endif
                                        >
                                            <x-nav-icon :name="$child['icon'] ?? 'default'" />
                                            <span x-show="!sidebarCollapsed">{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <a
                                href="{{ $menuHref($item['route']) }}"
                                @class([
                                    'flex w-full items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors',
                                    'bg-primary-muted text-primary' => $activeMenu === $item['key'],
                                    'text-text-muted hover:bg-surface-muted hover:text-text' => $activeMenu !== $item['key'],
                                ])
                                @if ($activeMenu === $item['key']) aria-current="page" @endif
                            >
                                <x-nav-icon :name="$item['icon'] ?? 'default'" />
                                <span x-show="!sidebarCollapsed">{{ $item['label'] }}</span>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mt-auto px-3 py-4 border-t border-border">
            <button type="button" class="hidden lg:flex w-full items-center gap-3 px-3 py-2 rounded-md text-sm nageeb-text-muted hover:bg-surface-muted" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('nageeb-sidebar', sidebarCollapsed ? 'collapsed' : 'expanded')" :aria-label="sidebarCollapsed ? 'توسيع القائمة' : 'طي القائمة'">
                <svg class="size-5 shrink-0 transition-transform" :class="sidebarCollapsed && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m15 18-6-6 6-6" stroke-width="1.7"/></svg>
                <span x-show="!sidebarCollapsed">طي القائمة</span>
            </button>
            <div class="px-3 pt-3" x-show="!sidebarCollapsed">
                <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                <span class="nageeb-caption">{{ $roleLabel }}</span>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 bg-surface/95 backdrop-blur border-b border-border">
            <div class="flex min-h-[var(--nageeb-topbar-height)] items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex items-center gap-3 min-w-0">
                    <label
                        for="dashboard-sidebar-toggle"
                        class="nageeb-btn nageeb-btn--ghost nageeb-btn--icon lg:hidden cursor-pointer"
                        aria-label="فتح القائمة"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <h1 class="text-lg sm:text-xl font-bold truncate">{{ $title }}</h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    <div class="relative" x-data="{ open: false }" dir="rtl">
                        <button
                            type="button"
                            class="relative nageeb-btn nageeb-btn--ghost nageeb-btn--icon"
                            @click="open = !open"
                            aria-label="الإشعارات"
                            :aria-expanded="open"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0" />
                            </svg>
                            @if ($unreadNotificationsCount > 0)
                                <span class="absolute -top-1 -start-1 min-w-5 h-5 px-1 bg-secondary text-xs flex items-center justify-center">{{ $unreadNotificationsCount }}</span>
                            @endif
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            @click.outside="open = false"
                            class="nageeb-dropdown absolute top-full mt-2 bg-surface border border-border shadow-md z-50 p-3"
                            role="menu"
                        >
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <p class="font-medium text-sm">الإشعارات</p>
                                @if ($unreadNotificationsCount > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-sm">تعليم الكل كمقروء</button>
                                    </form>
                                @endif
                            </div>
                            @forelse ($latestNotifications as $notification)
                                <a href="{{ $notification->data['url'] ?? '#' }}" class="block py-2 border-b border-border last:border-0 text-start">
                                    <p class="text-sm font-medium">{{ $notification->data['title'] ?? 'إشعار' }}</p>
                                    <p class="text-xs nageeb-text-muted">{{ $notification->data['body'] ?? '' }}</p>
                                </a>
                            @empty
                                <x-empty-state title="لا توجد إشعارات." />
                            @endforelse
                        </div>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nageeb-btn nageeb-btn--ghost text-sm">
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main @class(['nageeb-container py-6 sm:py-8 flex-1 w-full min-w-0', 'max-lg:pb-24' => $user->isTeacher()])>
            {{ $slot }}
        </main>
    </div>

    @if ($user->isTeacher())
        <nav class="lg:hidden fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 border-t border-border bg-surface/95 backdrop-blur px-2 pb-[env(safe-area-inset-bottom)]" aria-label="التنقل السريع">
            @foreach ([
                ['dashboard', 'الرئيسية', 'teacher.dashboard', 'home'],
                ['courses', 'المواد', 'teacher.courses.index', 'book'],
                ['enrollments', 'الطلاب', 'teacher.enrollments.index', 'users'],
                ['earnings', 'الأرباح', 'teacher.earnings.index', 'revenue'],
                ['settings', 'الإعدادات', 'teacher.settings.edit', 'settings'],
            ] as [$key, $label, $routeName, $icon])
                <a href="{{ route($routeName) }}" @class(['flex min-h-16 flex-col items-center justify-center gap-1 text-[0.65rem]', 'text-primary' => $activeMenu === $key, 'text-text-muted' => $activeMenu !== $key])>
                    <x-nav-icon :name="$icon" />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    @endif
</div>
