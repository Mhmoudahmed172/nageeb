@extends('layouts.app')

@section('title', 'معمل واجهة نجيب')

@section('content')
<div class="min-h-screen">
    <header class="sticky top-0 z-30 border-b border-border bg-surface/95 backdrop-blur">
        <div class="nageeb-container min-h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-md bg-primary text-white font-bold">ن</span>
                <div>
                    <h1 class="font-bold">معمل واجهة نجيب</h1>
                    <p class="nageeb-caption">صفحة داخلية · الإصدار 2.0</p>
                </div>
            </div>
            <x-badge variant="info">RTL First</x-badge>
        </div>
    </header>

    <main class="nageeb-container py-8 sm:py-12 space-y-14">
        <section class="max-w-3xl">
            <x-breadcrumbs :items="[['label' => 'نجيب', 'href' => '/'], ['label' => 'نظام التصميم']]" />
            <p class="nageeb-caption uppercase tracking-wider mt-8 mb-3">Nageeb Design Language</p>
            <h2 class="nageeb-display">تعلّم بوضوح.<br><span class="text-primary">تقدّم بثقة.</span></h2>
            <p class="text-lg nageeb-text-muted leading-relaxed mt-5 max-w-2xl">نظام واجهة عربي أصيل لمنصة تعليمية احترافية، مبني للوضوح والسرعة وسهولة الاستخدام عبر جميع الأدوار.</p>
        </section>

        <section>
            <h2 class="nageeb-heading-2 mb-5">الألوان والطباعة</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach ([
                    ['primary', 'أساسي'], ['accent', 'تمييز'], ['surface', 'سطح'], ['surface-muted', 'سطح هادئ'],
                    ['success', 'نجاح'], ['warning', 'تنبيه'], ['danger', 'خطر']
                ] as [$color, $label])
                    <div>
                        <div class="h-20 rounded-lg border border-border bg-{{ $color }}"></div>
                        <p class="text-sm font-semibold mt-2">{{ $label }}</p>
                        <p class="nageeb-caption">{{ $color }}</p>
                    </div>
                @endforeach
            </div>
            <x-card class="mt-6 grid gap-4">
                <p class="nageeb-display">عنوان عرض</p>
                <p class="nageeb-heading-1">عنوان رئيسي</p>
                <p class="nageeb-heading-2">عنوان قسم</p>
                <p class="nageeb-heading-3">عنوان فرعي</p>
                <p>نص أساسي مريح للقراءة في المحتوى التعليمي والواجهات.</p>
                <p class="nageeb-caption">وصف صغير أو معلومة مساندة</p>
            </x-card>
        </section>

        <section>
            <h2 class="nageeb-heading-2 mb-5">الأزرار والتغذية الراجعة</h2>
            <x-card>
                <div class="flex flex-wrap gap-3">
                    <x-button>حفظ التغييرات</x-button>
                    <x-button variant="secondary">إجراء ثانوي</x-button>
                    <x-button variant="outline">معاينة</x-button>
                    <x-button variant="ghost">تخطي</x-button>
                    <x-button variant="danger">حذف</x-button>
                    <x-button loading>جارٍ الحفظ</x-button>
                    <x-button variant="outline" icon-only aria-label="إعدادات">⋯</x-button>
                </div>
                <div class="flex flex-wrap gap-2 mt-6">
                    <x-badge>نشط</x-badge><x-badge variant="success">مكتمل</x-badge>
                    <x-badge variant="warning">قيد المراجعة</x-badge><x-badge variant="danger">مرفوض</x-badge>
                    <x-badge variant="info">معلومة</x-badge>
                </div>
                <div class="grid md:grid-cols-2 gap-3 mt-6">
                    <div class="nageeb-alert nageeb-alert--success">تم حفظ التغييرات بنجاح.</div>
                    <div class="nageeb-alert nageeb-alert--warning">يرجى مراجعة بيانات السعر.</div>
                </div>
            </x-card>
        </section>

        <section>
            <h2 class="nageeb-heading-2 mb-5">النماذج</h2>
            <x-card class="grid md:grid-cols-2 gap-5">
                <x-form-input label="عنوان المادة" name="demo_title" placeholder="مثال: الرياضيات — الصف الحادي عشر" required />
                <x-form-select label="الحالة" name="demo_status"><option>مسودة</option><option>منشور</option></x-form-select>
                <x-price-input label="السعر" name="demo_price" value="120" />
                <x-search-input placeholder="ابحث عن طالب أو مادة…" />
                <div class="md:col-span-2"><x-form-textarea label="الوصف" name="demo_description" rows="4" /></div>
                <x-form-checkbox label="إتاحة المعاينة المجانية" name="demo_preview" help="يمكن للطالب مشاهدة هذا الدرس قبل الاشتراك." />
                <x-form-toggle label="نشر المادة" name="demo_live" help="ستظهر المادة للطلاب مباشرة." checked />
                <div class="md:col-span-2"><x-file-upload label="رفع صورة الغلاف" name="demo_cover" help="PNG أو JPG، بحد أقصى 5MB" /></div>
            </x-card>
        </section>

        <section>
            <h2 class="nageeb-heading-2 mb-5">البطاقات والبيانات</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-card><div class="nageeb-stat"><span class="nageeb-stat__label">الطلاب النشطون</span><strong class="nageeb-stat__value">1,248</strong><span class="nageeb-stat__trend">↑ 12% هذا الشهر</span></div></x-card>
                <x-card><div class="nageeb-stat"><span class="nageeb-stat__label">الدروس المنشورة</span><strong class="nageeb-stat__value">86</strong><span class="nageeb-caption">ضمن 8 مواد</span></div></x-card>
                <x-card><div class="nageeb-stat"><span class="nageeb-stat__label">معدل الإكمال</span><strong class="nageeb-stat__value">74%</strong><x-progress value="74" :show-value="false" /></div></x-card>
                <x-card variant="muted"><div class="nageeb-stat"><span class="nageeb-stat__label">طلبات معلقة</span><strong class="nageeb-stat__value">12</strong><span class="nageeb-caption">تحتاج إلى مراجعة</span></div></x-card>
            </div>
            <x-card title="آخر التسجيلات" subtitle="عرض توضيحي لاستجابة الجدول على الهاتف" class="mt-5 overflow-hidden">
                <div class="nageeb-table-wrap">
                    <table class="nageeb-table nageeb-table--stack">
                        <thead><tr><th>الطالب</th><th>المادة</th><th>الخطة</th><th>الحالة</th></tr></thead>
                        <tbody>
                            <tr><td data-label="الطالب">سارة أحمد</td><td data-label="المادة">الرياضيات</td><td data-label="الخطة">المادة كاملة</td><td data-label="الحالة"><x-badge variant="success">نشط</x-badge></td></tr>
                            <tr><td data-label="الطالب">عمر خالد</td><td data-label="المادة">الفيزياء</td><td data-label="الخطة">الفصل الأول</td><td data-label="الحالة"><x-badge variant="warning">معلق</x-badge></td></tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </section>

        <section>
            <h2 class="nageeb-heading-2 mb-5">مكوّنات التعليم</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                <x-course-card title="الرياضيات — الصف الحادي عشر" teacher="أ. محمد السالم" badge="الأكثر نشاطاً" progress="68" />
                <x-course-card title="اللغة الإنجليزية — توجيهي" teacher="أ. دانا علي" progress="34" />
                <x-card title="تقدمك هذا الأسبوع" subtitle="استمر، أنت قريب من هدفك">
                    <p class="text-3xl font-mono font-semibold mb-5">4 / 6 <span class="text-sm nageeb-text-muted">دروس</span></p>
                    <x-progress value="66" label="الهدف الأسبوعي" />
                </x-card>
            </div>
            <div class="grid lg:grid-cols-2 gap-5 mt-5">
                <x-unit-accordion title="الوحدة الأولى: الاقترانات" meta="3 دروس" open>
                    <x-lesson-row index="01" title="مقدمة في الاقترانات" duration="12 دقيقة" status="مكتمل" />
                    <x-lesson-row index="02" title="المجال والمدى" duration="18 دقيقة" status="التالي" />
                    <x-lesson-row index="03" title="اختبار الوحدة" duration="10 أسئلة" />
                </x-unit-accordion>
                <x-card title="حالة التحميل">
                    <div class="grid gap-4">
                        <x-skeleton height="1.25rem" width="55%" />
                        <x-skeleton height="0.875rem" />
                        <x-skeleton height="0.875rem" width="80%" />
                        <div class="flex items-center gap-2 text-sm nageeb-text-muted"><span class="nageeb-spinner"></span> جارٍ تحميل المحتوى…</div>
                    </div>
                </x-card>
            </div>
        </section>

        <section>
            <h2 class="nageeb-heading-2 mb-5">الحالات والنوافذ</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <x-card><x-empty-state title="لا توجد دروس بعد" action-href="#" action-label="إضافة أول درس">ابدأ ببناء محتوى الوحدة خطوة بخطوة.</x-empty-state></x-card>
                <x-card>
                    <h3 class="nageeb-heading-3 mb-2">نافذة التأكيد</h3>
                    <p class="nageeb-text-muted text-sm mb-5">تتكيف كلوحة سفلية على الهاتف ونافذة مركزية على الشاشات الكبيرة.</p>
                    <x-button x-on:click="$dispatch('open-modal', 'demo-confirm')">فتح النافذة</x-button>
                </x-card>
            </div>
            <x-modal name="demo-confirm" title="نشر المادة؟" description="سيتمكن الطلاب من رؤية المادة ومحتواها المنشور.">
                <div class="nageeb-alert nageeb-alert--info">يمكنك إلغاء النشر لاحقاً من إعدادات المادة.</div>
                <x-slot:footer>
                    <x-button variant="ghost" x-on:click="$dispatch('close-modal', 'demo-confirm')">إلغاء</x-button>
                    <x-button x-on:click="$dispatch('close-modal', 'demo-confirm')">تأكيد النشر</x-button>
                </x-slot:footer>
            </x-modal>
        </section>
    </main>
</div>
@endsection
