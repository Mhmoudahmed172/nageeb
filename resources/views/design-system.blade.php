@extends('layouts.app')

@section('title', 'نجيب — نظام التصميم')

@section('content')
<div class="min-h-screen">
    {{-- Header --}}
    <header class="bg-primary text-text-inverse">
        <div class="nageeb-container py-6 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold">نجيب</h1>
                <p class="text-sm opacity-80 mt-1">منصة تعليمية إلكترونية</p>
            </div>
            <span class="nageeb-badge bg-secondary/20 text-secondary-light border border-secondary/30">
                نظام التصميم
            </span>
        </div>
    </header>

    <main class="nageeb-container py-10 space-y-12">
        {{-- Hero --}}
        <section class="text-center max-w-2xl mx-auto">
            <h2 class="nageeb-title-hero mb-4">تعلّم بثقة وراحة</h2>
            <p class="text-text-muted text-lg leading-relaxed">
                نظام تصميم رسمي وموثوق يمنح تجربة تعليمية هادئة ومريحة —
                بدون إزعاج بصري أو طابع لعبة.
            </p>
        </section>

        {{-- Color Palette --}}
        <section>
            <h3 class="nageeb-title-section mb-6">لوحة الألوان</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="nageeb-swatch">
                    <div class="nageeb-swatch__color bg-primary"></div>
                    <span class="nageeb-swatch__label">Primary</span>
                    <span class="nageeb-swatch__hex">#4B2E83</span>
                </div>
                <div class="nageeb-swatch">
                    <div class="nageeb-swatch__color bg-secondary"></div>
                    <span class="nageeb-swatch__label">Secondary</span>
                    <span class="nageeb-swatch__hex">#C9A24B</span>
                </div>
                <div class="nageeb-swatch">
                    <div class="nageeb-swatch__color bg-support"></div>
                    <span class="nageeb-swatch__label">Support</span>
                    <span class="nageeb-swatch__hex">#7BA895</span>
                </div>
                <div class="nageeb-swatch">
                    <div class="nageeb-swatch__color bg-background border border-border"></div>
                    <span class="nageeb-swatch__label">Background</span>
                    <span class="nageeb-swatch__hex">#FAF9F6</span>
                </div>
                <div class="nageeb-swatch">
                    <div class="nageeb-swatch__color bg-text"></div>
                    <span class="nageeb-swatch__label">Text</span>
                    <span class="nageeb-swatch__hex">#1E1B2E</span>
                </div>
                <div class="nageeb-swatch">
                    <div class="nageeb-swatch__color bg-alert"></div>
                    <span class="nageeb-swatch__label">Alert</span>
                    <span class="nageeb-swatch__hex">#B5504A</span>
                </div>
            </div>
        </section>

        {{-- Typography --}}
        <section>
            <h3 class="nageeb-title-section mb-6">الخطوط</h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="nageeb-card">
                    <p class="text-sm text-text-dim mb-2">IBM Plex Sans Arabic — عناوين ونصوص</p>
                    <p class="text-3xl font-bold mb-3">مرحباً بك في نجيب</p>
                    <p class="text-text-muted leading-relaxed">
                        خط عربي واضح ورسمي يناسب المحتوى التعليمي. يُستخدم لكل العناوين
                        والفقرات والواجهات.
                    </p>
                </div>
                <div class="nageeb-card">
                    <p class="text-sm text-text-dim mb-2">IBM Plex Mono — أرقام</p>
                    <p class="text-3xl font-bold num mb-3">١٢٣٤٥ · 98.7%</p>
                    <p class="text-text-muted leading-relaxed">
                        يُستخدم للدرجات والإحصائيات والأسعار —
                        <span class="num font-mono text-primary">1,250</span> طالب،
                        <span class="num font-mono text-support">95</span> درجة.
                    </p>
                </div>
            </div>
        </section>

        {{-- Buttons --}}
        <section>
            <h3 class="nageeb-title-section mb-6">الأزرار</h3>
            <div class="flex flex-wrap gap-4">
                <button type="button" class="nageeb-btn nageeb-btn--primary">ابدأ التعلّم</button>
                <button type="button" class="nageeb-btn nageeb-btn--secondary">استكشف الدورات</button>
                <button type="button" class="nageeb-btn nageeb-btn--outline">تسجيل الدخول</button>
            </div>
        </section>

        {{-- Cards & Badges --}}
        <section>
            <h3 class="nageeb-title-section mb-6">البطاقات والشارات</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="nageeb-card nageeb-card--elevated">
                    <span class="nageeb-badge nageeb-badge--primary mb-3">رياضيات</span>
                    <h4 class="font-semibold text-lg mb-2">أساسيات الجبر</h4>
                    <p class="text-text-muted text-sm mb-4">دورة تأسيسية للمرحلة الثانوية</p>
                    <p class="num text-2xl font-semibold text-primary">4.8<span class="text-sm text-text-dim">/5</span></p>
                </div>
                <div class="nageeb-card">
                    <span class="nageeb-badge nageeb-badge--support mb-3">مكتمل</span>
                    <h4 class="font-semibold text-lg mb-2">اللغة العربية</h4>
                    <p class="text-text-muted text-sm mb-4">قواعد النحو والصرف</p>
                    <p class="num text-2xl font-semibold text-support">100<span class="text-sm text-text-dim">%</span></p>
                </div>
                <div class="nageeb-card">
                    <span class="nageeb-badge nageeb-badge--secondary mb-3">جديد</span>
                    <h4 class="font-semibold text-lg mb-2">العلوم</h4>
                    <p class="text-text-muted text-sm mb-4">مقدمة في الفيزياء</p>
                    <p class="num text-2xl font-semibold text-secondary">12<span class="text-sm text-text-dim"> درس</span></p>
                </div>
            </div>
        </section>

        {{-- Alert --}}
        <section>
            <h3 class="nageeb-title-section mb-6">التنبيهات</h3>
            <div class="nageeb-alert nageeb-alert--error max-w-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-medium">تنبيه</p>
                    <p class="mt-1 opacity-90">يرجى إكمال الملف الشخصي قبل بدء الدورة.</p>
                </div>
            </div>
        </section>

        {{-- RTL Demo --}}
        <section class="nageeb-card bg-surface-muted">
            <h3 class="nageeb-title-section mb-4">دعم RTL</h3>
            <p class="text-text-muted mb-4">
                الواجهة بالكامل من اليمين إلى اليسار — العناوين، القوائم، والمحاذاة.
            </p>
            <ul class="space-y-2 text-sm">
                <li class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-primary"></span>
                    <span>اتجاه النص: RTL</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-secondary"></span>
                    <span>اللغة: العربية</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-support"></span>
                    <span>html lang="ar" dir="rtl"</span>
                </li>
            </ul>
        </section>
    </main>

    <footer class="border-t border-border mt-12">
        <div class="nageeb-container py-6 text-center text-text-dim text-sm">
            <p>© {{ date('Y') }} نجيب — جميع الحقوق محفوظة</p>
        </div>
    </footer>
</div>
@endsection
