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
            ['key' => 'dashboard', 'label' => 'لوحة الأداء', 'route' => 'teacher.dashboard'],
            [
                'label' => 'إدارة المحتوى',
                'children' => [
                    ['key' => 'courses', 'label' => 'مقرراتي', 'route' => 'teacher.courses.index'],
                ],
            ],
            [
                'label' => 'تفاعل الطلاب',
                'children' => [
                    ['key' => 'packages', 'label' => 'باقات الاشتراك', 'route' => 'teacher.packages.index'],
                    ['key' => 'subscription-requests', 'label' => 'طلبات الاشتراك', 'route' => 'teacher.subscription-requests.index'],
                    ['key' => 'enrollments', 'label' => 'الملتحقون', 'route' => 'teacher.enrollments.index'],
                    ['key' => 'engagement', 'label' => 'التفاعل', 'route' => 'teacher.interactions.index'],
                ],
            ],
            [
                'label' => 'الأرباح والمستحقات',
                'children' => [
                    ['key' => 'earnings', 'label' => 'الأرباح', 'route' => 'teacher.earnings.index'],
                    ['key' => 'payouts', 'label' => 'التسويات والسحوبات', 'route' => 'teacher.payouts.index'],
                ],
            ],
            [
                'label' => 'إعدادات الحساب',
                'children' => [
                    ['key' => 'profile', 'label' => 'ملف المدرّس', 'route' => 'teacher.profile.edit'],
                    ['key' => 'settings', 'label' => 'إعدادات الحساب', 'route' => 'teacher.settings.edit'],
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

<div class="min-h-screen lg:flex">
    <input
        type="checkbox"
        id="dashboard-sidebar-toggle"
        class="peer sr-only"
        autocomplete="off"
    >

    <label
        for="dashboard-sidebar-toggle"
        class="fixed inset-0 z-40 bg-text opacity-40 hidden max-lg:peer-checked:block"
        aria-hidden="true"
    ></label>

    <aside
        class="fixed top-0 start-0 z-50 flex h-dvh w-[min(18rem,90vw)] shrink-0 translate-x-full flex-col overflow-y-auto bg-surface border-e border-border transition-transform max-lg:peer-checked:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0"
    >
        <div class="bg-primary text-text-inverse px-5 py-5 flex items-start justify-between gap-3">
            <div>
                <a href="{{ url('/') }}" class="text-text-inverse hover:text-text-inverse hover:opacity-90">
                    <span class="text-xl font-bold">نجيب</span>
                </a>
                <p class="text-sm opacity-80 mt-0.5">لوحة تحكم {{ $roleLabel }}</p>
            </div>
            <label
                for="dashboard-sidebar-toggle"
                class="nageeb-btn nageeb-btn--secondary text-sm py-2 px-3 lg:hidden cursor-pointer"
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
                            <p class="nageeb-text-dim text-xs font-medium px-3 mb-2">{{ $item['label'] }}</p>
                            <ul class="flex flex-col gap-1">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a
                                            href="{{ $menuHref($child['route']) }}"
                                            @class([
                                                'flex w-full items-center px-3 py-2 text-sm font-medium',
                                                'bg-primary text-text-inverse hover:text-text-inverse' => $activeMenu === $child['key'],
                                                'text-text hover:bg-primary-muted hover:text-primary' => $activeMenu !== $child['key'],
                                            ])
                                            @if ($activeMenu === $child['key']) aria-current="page" @endif
                                        >
                                            {{ $child['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <a
                                href="{{ $menuHref($item['route']) }}"
                                @class([
                                    'flex w-full items-center px-3 py-2 text-sm font-medium',
                                    'bg-primary text-text-inverse hover:text-text-inverse' => $activeMenu === $item['key'],
                                    'text-text hover:bg-primary-muted hover:text-primary' => $activeMenu !== $item['key'],
                                ])
                                @if ($activeMenu === $item['key']) aria-current="page" @endif
                            >
                                {{ $item['label'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mt-auto px-5 py-4 border-t border-border">
            <p class="text-sm font-medium">{{ $user->name }}</p>
            <span class="nageeb-badge nageeb-badge--primary mt-2">{{ $roleLabel }}</span>
        </div>
    </aside>

    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <header class="bg-surface border-b border-border">
            <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-5 sm:py-4 flex-wrap">
                <div class="flex items-center gap-3 min-w-0">
                    <label
                        for="dashboard-sidebar-toggle"
                        class="nageeb-btn nageeb-btn--outline py-2 px-3 lg:hidden cursor-pointer"
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
                            class="relative nageeb-btn nageeb-btn--outline py-2 px-3"
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
                    <span class="text-sm nageeb-text-muted hidden sm:inline">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nageeb-btn nageeb-btn--secondary text-sm py-2 px-3 sm:px-4">
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="nageeb-container py-6 sm:py-10 flex-1 w-full min-w-0">
            {{ $slot }}
        </main>
    </div>
</div>
