@extends('layouts.app')

@section('title', 'نجيب — نظام التصميم')

@section('content')

<div class="min-h-screen bg-background pb-24">
    <header class="sticky top-0 z-50 border-b border-border bg-surface/95 backdrop-blur-md">
        <div class="nageeb-container flex h-[4.5rem] items-center justify-between gap-4">
            <a href="#top" class="nageeb-public-nav__brand">
                <span class="nageeb-mark">ن</span>
                <span>نجيب</span>
            </a>
            <nav class="nageeb-public-nav__links" aria-label="أقسام نظام التصميم">
                <a href="#brand">الأساس</a>
                <a href="#typography">الخطوط</a>
                <a href="#components">المكونات</a>
                <a href="#educational">التجربة التعليمية</a>
                <a href="#navigation">التنقّل</a>
            </nav>
            <div class="flex items-center gap-2 sm:gap-3">
                <x-badge variant="primary" class="hidden sm:inline-flex">عرض داخلي</x-badge>
                <x-button variant="primary" size="sm" href="#educational">استكشف النظام</x-button>
            </div>
        </div>
    </header>

    <main id="top" class="nageeb-container space-y-24 md:space-y-32">
        {{-- Hero --}}
        <section class="nageeb-hero">
            <div class="nageeb-hero__copy">
                <p class="nageeb-kicker">منصة نجيب التعليمية</p>
                <h1 class="nageeb-type-display mt-4">تعلّم بطريقة أوضح.<br>وابنِ مستقبلك بثقة.</h1>
                <p class="nageeb-type-body-lg nageeb-text-muted mt-5">
                    نظام تصميم عربي يجمع البساطة والتقنية وتجربة التعلّم الحديثة — للطلاب، والمعلمين، وأولياء الأمور في فلسطين.
                </p>
                <div class="nageeb-hero__actions">
                    <x-button variant="primary" size="lg" href="#educational">شاهد التجربة التعليمية</x-button>
                    <x-button variant="outline" size="lg" href="#brand">تعرّف على الأساس البصري</x-button>
                </div>
            </div>

            <div class="nageeb-hero__stage">
                <div class="nageeb-hero__photo nageeb-media">
                    <x-nageeb-img path="hero/hero-student-studying.png" alt="طالب يدرس في بيئة تعليمية هادئة" />
                </div>

                <article class="nageeb-hero__float nageeb-hero__float--course">
                    <div class="flex gap-3">
                        <x-nageeb-img path="courses/arabic.png" alt="" class="size-14 rounded-md object-cover" />
                        <div class="min-w-0">
                            <p class="nageeb-type-caption">اللغة العربية</p>
                            <p class="nageeb-type-label text-text">الصف الحادي عشر</p>
                            <p class="nageeb-type-caption mt-1">أ. محمود طارق</p>
                        </div>
                    </div>
                </article>

                <aside class="nageeb-hero__float nageeb-hero__float--progress">
                    <p class="nageeb-type-caption mb-2">تقدمك هذا الأسبوع</p>
                    <x-progress :value="72" label="العربية" />
                </aside>

                <aside class="nageeb-hero__float nageeb-hero__float--teacher">
                    <x-nageeb-img path="teachers/mahmoud.png" alt="أ. محمود طارق" class="nageeb-avatar" />
                    <div class="min-w-0">
                        <p class="nageeb-type-label text-text truncate">أ. محمود طارق</p>
                        <p class="nageeb-type-caption">1,250 طالبًا</p>
                    </div>
                </aside>
            </div>
        </section>

        {{-- 1. Brand --}}
        <x-ds-section id="brand" index="01" title="أساس العلامة" lede="كريمي دافئ للخلفية، بني عميق للعناوين، وتراكوتا للإجراء. الرمال لمسات خفيفة. الصور تروي بيئة التعلّم دون أن تطغى الألوان على الصفحة.">
            <div class="nageeb-swatch-row mb-10">
                <div class="nageeb-swatch-hero bg-chocolate">
                    <p class="text-sm text-white/80">Chocolate</p>
                    <p class="text-2xl font-bold mt-1">#2B1D18</p>
                    <p class="text-sm text-white/80 mt-2">التذييل، العناوين القوية</p>
                </div>
                <div class="nageeb-swatch-hero">
                    <p class="text-sm text-white/80">Primary</p>
                    <p class="text-2xl font-bold mt-1">#9A5B43</p>
                    <p class="text-sm text-white/80 mt-2">الأزرار، الروابط، التقدّم، الحالة النشطة</p>
                </div>
                <div class="nageeb-color-use">
                    <div class="h-10 rounded-md bg-primary-muted border border-border"></div>
                    <strong>Primary soft</strong>
                    <span class="nageeb-text-muted">خلفيات خفيفة</span>
                </div>
                <div class="nageeb-color-use">
                    <div class="h-10 rounded-md bg-accent"></div>
                    <strong>Accent</strong>
                    <span class="nageeb-text-muted">إنجاز وتمييز</span>
                </div>
                <div class="nageeb-color-use">
                    <div class="h-10 rounded-md bg-success"></div>
                    <strong>Success</strong>
                    <span class="nageeb-text-muted">اكتمال الدرس</span>
                </div>
                <div class="nageeb-color-use">
                    <div class="h-10 rounded-md bg-surface border border-border"></div>
                    <strong>Surface</strong>
                    <span class="nageeb-text-muted">البطاقات</span>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <h3 class="nageeb-type-h4 mb-4">نظام الصور</h3>
                    <div class="nageeb-imagery-grid">
                        <div class="nageeb-media"><x-nageeb-img path="hero/hero-student-studying.png" alt="صورة البطل التعليمية" /></div>
                        <div class="nageeb-media"><x-nageeb-img path="courses/mathematics.png" alt="غلاف الرياضيات" /></div>
                        <div class="nageeb-media"><x-nageeb-img path="teachers/layla.png" alt="صورة معلمة" /></div>
                        <div class="nageeb-media"><x-nageeb-img path="illustrations/learning.png" alt="رسم التعلّم" /></div>
                        <div class="nageeb-media"><x-nageeb-img path="achievements/certificate.png" alt="رسم الشهادة" /></div>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="nageeb-type-h4">لغة الرسوم</h3>
                    <p class="nageeb-type-body nageeb-text-muted">أشكال هندسية ناعمة، عمق خفيف، ألوان كريمية وكحلية. ودّية دون أن تكون طفولية، ومناسبة للمنطقة.</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (['illustrations/learning.png' => 'تعلّم', 'illustrations/exams.png' => 'اختبارات', 'illustrations/achievement.png' => 'إنجاز', 'illustrations/progress.png' => 'تقدّم', 'illustrations/empty.png' => 'فراغ', 'illustrations/certificate.png' => 'شهادة'] as $path => $label)
                            <figure class="text-center">
                                <div class="aspect-square rounded-md border border-border bg-surface overflow-hidden">
                                    <x-nageeb-img :path="$path" :alt="$label" class="w-full h-full object-cover" />
                                </div>
                                <figcaption class="nageeb-type-caption mt-2">{{ $label }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                        <div class="nageeb-color-use"><strong>Warning</strong><span class="font-mono text-xs">#D97706</span></div>
                        <div class="nageeb-color-use"><strong>Danger</strong><span class="font-mono text-xs">#DC2626</span></div>
                        <div class="nageeb-color-use"><strong>نص أساسي</strong><span class="font-mono text-xs">#0F172A</span></div>
                        <div class="nageeb-color-use"><strong>نص ثانوي</strong><span class="font-mono text-xs">#64748B</span></div>
                    </div>
                </div>
            </div>
        </x-ds-section>

        {{-- 2. Typography --}}
        <x-ds-section id="typography" index="02" title="الخطوط" lede="IBM Plex Sans Arabic للعناوين والنصوص. العناوين قوية وواضحة، والجسم مريح للقراءة الطويلة.">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <div class="nageeb-card space-y-7">
                    @foreach ([
                        ['Display', 'nageeb-type-display', 'تعلّم بطريقة أوضح'],
                        ['H1', 'nageeb-type-h1', 'اكتشف مواد معلّمك'],
                        ['H2', 'nageeb-type-h2', 'الفصل الأول — اللغة العربية'],
                        ['H3', 'nageeb-type-h3', 'الوحدة الثانية: النحو'],
                        ['H4', 'nageeb-type-h4', 'الدرس 4: المفعول به'],
                    ] as [$label, $class, $sample])
                        <div>
                            <p class="nageeb-type-caption font-mono mb-1">{{ $label }}</p>
                            <p class="{{ $class }}">{{ $sample }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="nageeb-card nageeb-card--muted space-y-6">
                    <div>
                        <p class="nageeb-type-caption font-mono mb-2">Body Large</p>
                        <p class="nageeb-type-body-lg">يساعدك نجيب على متابعة دروسك واختباراتك وتقدمك في مكان واحد، بواجهة عربية هادئة.</p>
                    </div>
                    <div>
                        <p class="nageeb-type-caption font-mono mb-2">Body</p>
                        <p class="nageeb-type-body nageeb-text-muted">أكمل درس المفعول به ثم انتقل إلى ورقة العمل المرفقة قبل اختبار الوحدة.</p>
                    </div>
                    <div>
                        <p class="nageeb-type-caption font-mono mb-2">Body Small</p>
                        <p class="nageeb-type-body-sm nageeb-text-muted">آخر نشاط: قبل ساعتين · تم تسليم الاختبار.</p>
                    </div>
                    <div>
                        <p class="nageeb-type-caption font-mono mb-2">Caption / Label</p>
                        <p class="nageeb-type-label">مادة · الصف الحادي عشر</p>
                        <p class="nageeb-type-caption mt-1">حقوق العرض محفوظة لمنصة نجيب</p>
                    </div>
                </div>
            </div>
        </x-ds-section>

        {{-- 3. Buttons --}}
        <x-ds-section id="components" index="03" title="الأزرار" lede="الزمرد للإجراء الأساسي فقط. الباقي هادئ حتى تبقى الواجهة تعليمية لا إدارية.">
            <div class="nageeb-continue mb-8">
                <div class="nageeb-continue__thumb nageeb-media">
                    <x-nageeb-img path="courses/dashboard-cover.png" alt="" />
                </div>
                <div>
                    <p class="nageeb-type-caption">أكمل التعلّم</p>
                    <h3 class="nageeb-type-h4">المفعول به — تطبيقات</h3>
                    <p class="nageeb-type-body-sm nageeb-text-muted mt-1">العربية · الصف الحادي عشر · تبقّى 8 دقائق</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button variant="primary">متابعة الدرس</x-button>
                    <x-button variant="outline">تفاصيل المادة</x-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-button variant="primary">حفظ الخطة</x-button>
                <x-button variant="primary" loading="true">جاري الحفظ</x-button>
                <x-button variant="secondary">إلغاء</x-button>
                <x-button variant="outline">تصفح المواد</x-button>
                <x-button variant="ghost">تخطي</x-button>
                <x-button variant="danger">حذف المسودة</x-button>
                <x-button variant="primary" size="sm">صغير</x-button>
                <x-button variant="primary" size="lg">كبير</x-button>
            </div>
        </x-ds-section>

        {{-- 4. Forms --}}
        <x-ds-section id="forms" index="04" title="حقول الإدخال" lede="نماذج بهوية طالب حقيقية: الاسم، المنطقة، والصف. الأخطاء واضحة دون أن تصرخ.">
            <div class="nageeb-card max-w-4xl">
                <h3 class="nageeb-type-h4 mb-6">تسجيل طالب جديد</h3>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-form-input label="الاسم الكامل" name="ds-name" value="سارة خالد منصور" />
                    <x-form-input label="البريد الإلكتروني" name="ds-email" type="email" help="يُستخدم لتأكيد الحساب وإشعارات الاختبارات." required="true" />
                    <x-form-input label="رقم الهاتف" name="ds-phone" value="059" help="أدخل رقمًا من تسع خانات." />
                    <x-form-select label="المنطقة" name="ds-region">
                        <option>قطاع غزة</option>
                        <option selected>الضفة الغربية</option>
                    </x-form-select>
                    <div class="md:col-span-2">
                        <x-form-textarea label="نبذة قصيرة للطالب" name="ds-bio" placeholder="الصف، المادة الأهم، وهدف هذا الفصل..." />
                    </div>
                    <label class="nageeb-check">
                        <input type="checkbox" checked>
                        <span>أوافق على شروط الاستخدام وسياسة الخصوصية</span>
                    </label>
                </div>
            </div>
        </x-ds-section>

        {{-- 5. Cards --}}
        <x-ds-section id="cards" index="05" title="البطاقات" lede="بطاقات المادة والمعلّم وخطة الوصول هي قلب المنتج. كل بطاقة تحكي حالة حقيقية، لا مثالًا فارغًا.">
            <h3 class="nageeb-type-h4 mb-4">بطاقات المواد</h3>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 mb-10">
                <x-course-card
                    variant="marketplace"
                    title="اللغة العربية"
                    teacher="أ. محمود طارق"
                    avatar="teachers/mahmoud.png"
                    image="courses/arabic.png"
                    subject="لغة عربية"
                    grade="الحادي عشر"
                    lessons="32 درسًا"
                    students="1,250 طالبًا"
                    badge="الأكثر التحاقًا"
                    cta="عرض المادة"
                />
                <x-course-card
                    variant="student"
                    title="الرياضيات"
                    teacher="أ. ليلى عبد الرحمن"
                    avatar="teachers/layla.png"
                    image="courses/mathematics.png"
                    subject="رياضيات"
                    grade="الحادي عشر"
                    progress="72"
                    cta="متابعة التعلّم"
                />
                <x-course-card
                    variant="teacher"
                    title="الفيزياء"
                    teacher="أ. سامي نبيل"
                    avatar="teachers/sami.png"
                    image="courses/physics.png"
                    subject="فيزياء"
                    grade="التوجيهي العلمي"
                    students="840 طالبًا"
                    lessons="28 درسًا"
                    badge="منشورة"
                />
            </div>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] mb-10">
                <x-course-card
                    variant="featured"
                    title="الكيمياء العضوية — مراجعة شاملة"
                    teacher="أ. سامي نبيل"
                    avatar="teachers/sami.png"
                    image="courses/chemistry.png"
                    subject="كيمياء"
                    grade="التوجيهي"
                    lessons="24 درسًا"
                    students="640 طالبًا"
                    badge="مادة مميزة"
                    cta="ابدأ من هنا"
                />
                <div class="space-y-4">
                    <x-course-card
                        variant="compact"
                        title="اللغة الإنجليزية"
                        teacher="أ. ليلى عبد الرحمن"
                        image="courses/english.png"
                        progress="34"
                    />
                    <x-course-card
                        variant="compact"
                        title="الرياضيات — الهندسة"
                        teacher="أ. ليلى عبد الرحمن"
                        image="courses/mathematics.png"
                        badge="درس جديد"
                    />
                </div>
            </div>

            <h3 class="nageeb-type-h4 mb-4">بطاقات المعلمين</h3>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 mb-10">
                <x-teacher-card
                    variant="showcase"
                    name="أ. محمود طارق"
                    subject="اللغة العربية · المرحلة الثانوية"
                    photo="teachers/mahmoud.png"
                    students="1,250"
                    courses="4"
                    rating="4.9"
                    bio="يبني مادته على القراءة الدقيقة والتدريب المتكرر، مع خطط وصول منفصلة لكل فصل."
                />
                <x-teacher-card
                    variant="showcase"
                    name="أ. ليلى عبد الرحمن"
                    subject="الرياضيات والإنجليزية"
                    photo="teachers/layla.png"
                    students="980"
                    courses="3"
                    rating="4.8"
                    bio="تشرح المفاهيم خطوة بخطوة، وتركّز على بناء ثقة الطالب قبل الاختبار."
                />
                <x-teacher-card
                    variant="showcase"
                    name="أ. سامي نبيل"
                    subject="الفيزياء والكيمياء"
                    photo="teachers/sami.png"
                    students="840"
                    courses="5"
                    rating="4.9"
                    bio="معلّم مستقل يدير مواده وخطط اشتراكه بنفسه لطلابه في غزة والضفة."
                />
            </div>

            <h3 class="nageeb-type-h4 mb-3">خطط الوصول</h3>
            <p class="nageeb-type-body-sm nageeb-text-muted mb-5 max-w-2xl">الخطة ملك المعلّم ومادته — ليست باقة موحّدة للمنصة. الأسعار تختلف حسب المنطقة، والتغطية حسب الفصل أو المادة كاملة.</p>
            <div class="grid gap-5 md:grid-cols-3">
                <x-plan-card
                    title="الفصل الأول"
                    course="العربية"
                    grade="الصف الحادي عشر"
                    gaza-price="49"
                    west-bank-price="59"
                    duration="حتى نهاية الفصل الأول"
                    lessons="16 درسًا"
                    :includes="['دروس الفيديو', 'اختبارات الوحدة', 'المرفقات']"
                />
                <x-plan-card
                    title="المادة كاملة"
                    course="العربية"
                    grade="الصف الحادي عشر"
                    gaza-price="89"
                    west-bank-price="109"
                    duration="العام الدراسي"
                    lessons="32 درسًا"
                    badge="الأكثر اختيارًا"
                    :featured="true"
                    :includes="['الفصلان معًا', 'جميع الاختبارات', 'شهادة إتمام المادة']"
                />
                <x-plan-card
                    title="الفصل الثاني"
                    course="العربية"
                    grade="الصف الحادي عشر"
                    gaza-price="49"
                    west-bank-price="59"
                    duration="حتى نهاية الفصل الثاني"
                    lessons="16 درسًا"
                    :includes="['دروس الفيديو', 'اختبار نهاية الفصل', 'أوراق العمل']"
                />
            </div>
        </x-ds-section>

        {{-- 6. Data --}}
        <x-ds-section id="data" index="06" title="البيانات" lede="الأرقام تخدم القرار: تقدّم الطالب، حالة الاشتراك، ونتيجة الاختبار. بلا كثافة تحليلية زائدة.">
            <div class="grid gap-4 sm:grid-cols-3 mb-8">
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">طلاب أ. محمود هذا الفصل</p>
                    <p class="nageeb-stat__value tabular-nums">1,250</p>
                    <p class="nageeb-stat__trend">+48 هذا الأسبوع</p>
                </div>
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">اشتراكات نشطة</p>
                    <p class="nageeb-stat__value tabular-nums">312</p>
                    <p class="nageeb-type-caption mt-1">غزة 180 · الضفة 132</p>
                </div>
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">متوسط نتيجة الاختبار</p>
                    <p class="nageeb-stat__value tabular-nums">86%</p>
                    <p class="nageeb-type-caption mt-1">اختبار النحو — الوحدة 2</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                <x-card title="الحالات" variant="flat">
                    <div class="flex flex-wrap gap-2">
                        <x-badge variant="primary">منشورة</x-badge>
                        <x-badge variant="secondary">مسودة</x-badge>
                        <x-badge variant="success">مكتمل</x-badge>
                        <x-badge variant="warning">قيد الدفع</x-badge>
                        <x-badge variant="danger">مرفوض</x-badge>
                        <x-badge variant="info">مراجعة</x-badge>
                    </div>
                    <div class="mt-6">
                        <x-progress :value="72" label="تقدم سارة في العربية" />
                    </div>
                </x-card>

                <div class="nageeb-card !p-0 overflow-hidden">
                    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                        <h3 class="nageeb-type-h4">أحدث التحاق</h3>
                        <x-button variant="ghost" size="sm">عرض الكل</x-button>
                    </div>
                    <div class="nageeb-table-wrap">
                        <table class="nageeb-table">
                            <thead>
                                <tr>
                                    <th>الطالب</th>
                                    <th>المادة</th>
                                    <th>الخطة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-label="الطالب">سارة خالد</td>
                                    <td data-label="المادة">العربية</td>
                                    <td data-label="الخطة">الفصل الأول · غزة</td>
                                    <td data-label="الحالة"><x-badge variant="success">نشط</x-badge></td>
                                </tr>
                                <tr>
                                    <td data-label="الطالب">يوسف منصور</td>
                                    <td data-label="المادة">الفيزياء</td>
                                    <td data-label="الخطة">المادة كاملة · الضفة</td>
                                    <td data-label="الحالة"><x-badge variant="warning">قيد الدفع</x-badge></td>
                                </tr>
                                <tr>
                                    <td data-label="الطالب">نور تيسير</td>
                                    <td data-label="المادة">الرياضيات</td>
                                    <td data-label="الخطة">الفصل الثاني · غزة</td>
                                    <td data-label="الحالة"><x-badge variant="success">نشط</x-badge></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-ds-section>

        {{-- 7. Feedback --}}
        <x-ds-section id="feedback" index="07" title="التغذية الراجعة" lede="النجاح هادئ، والخطأ واضح، والحالات الفارغة تدعو للخطوة التالية بصورة تعليمية صغيرة.">
            <div class="grid gap-4 md:grid-cols-2 mb-8">
                <div class="nageeb-alert nageeb-alert--success">
                    <div>
                        <p class="font-semibold">تم حفظ خطة الوصول</p>
                        <p class="mt-1">أصبحت أسعار غزة والضفة ظاهرة لطلاب مادة العربية.</p>
                    </div>
                </div>
                <div class="nageeb-alert nageeb-alert--error">
                    <div>
                        <p class="font-semibold">تعذّر تسليم الاختبار</p>
                        <p class="mt-1">تحقق من الاتصال ثم أعد الإرسال. إجاباتك محفوظة.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <x-card>
                    <x-empty-state title="لا توجد مواد بعد" image="illustrations/empty.png" action-href="#" action-label="إنشاء مادة">ابدأ بمادة واحدة واضحة، ثم أضف الفصول والدروس.</x-empty-state>
                </x-card>
                <x-card>
                    <x-empty-state title="لا توجد اختبارات قادمة" image="illustrations/exams.png">عندما ينشر معلّمك اختبارًا سيظهر هنا مع موعده.</x-empty-state>
                </x-card>
                <x-card>
                    <x-empty-state title="لا توجد اشتراكات" image="illustrations/learning.png" action-href="#" action-label="عرض الخطط">اختر خطة المعلّم المناسبة لمنطقتك.</x-empty-state>
                </x-card>
                <x-card>
                    <x-empty-state title="لم تبدأ رحلة التعلّم بعد" image="illustrations/progress.png" action-href="#" action-label="ابدأ أول درس">أول درس قصير يكفي لفتح التقدّم.</x-empty-state>
                </x-card>
            </div>
        </x-ds-section>

        {{-- 8. Educational --}}
        <x-ds-section id="educational" index="08" title="التجربة التعليمية" lede="من متابعة الدرس إلى الاختبار والشهادة: تركيبات جاهزة للطالب والمعلّم، بهوية واحدة.">
            <h3 class="nageeb-type-h4 mb-4">تجربة الطالب</h3>
            <div class="grid gap-5 lg:grid-cols-3 mb-8">
                <div class="nageeb-stat-tile flex items-center gap-3">
                    <x-nageeb-img path="illustrations/streak.png" alt="" class="size-14 object-contain" />
                    <div>
                        <p class="nageeb-type-caption">سلسلة الدراسة</p>
                        <p class="nageeb-type-h3 tabular-nums">12 يومًا</p>
                    </div>
                </div>
                <div class="nageeb-stat-tile flex items-center gap-3">
                    <x-nageeb-img path="illustrations/achievement.png" alt="" class="size-14 object-contain" />
                    <div>
                        <p class="nageeb-type-caption">آخر إنجاز</p>
                        <p class="nageeb-type-h4">إتمام وحدة النحو</p>
                    </div>
                </div>
                <div class="nageeb-stat-tile flex items-center gap-3">
                    <x-nageeb-img path="achievements/certificate.png" alt="" class="size-14 object-contain" />
                    <div>
                        <p class="nageeb-type-caption">شهادة جاهزة</p>
                        <p class="nageeb-type-h4">الفصل الأول — العربية</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2 mb-12">
                <x-lesson-player
                    title="المفعول به — تطبيقات"
                    course="اللغة العربية"
                    semester="الفصل الأول"
                    unit="الوحدة الثانية: النحو"
                    progress="62"
                    next-lesson="التوابع"
                />
                <div class="space-y-5">
                    <x-lesson-player
                        title="قوانين نيوتن"
                        course="الفيزياء"
                        semester="الفصل الأول"
                        unit="الميكانيكا"
                        state="protected"
                        thumbnail="courses/physics.png"
                    />
                    <div class="nageeb-continue">
                        <div class="nageeb-continue__thumb nageeb-media">
                            <x-nageeb-img path="exams/exam-thumbnail.png" alt="" />
                        </div>
                        <div>
                            <p class="nageeb-type-caption">اختبار قادم</p>
                            <h3 class="nageeb-type-h4">اختبار النحو — الوحدة 2</h3>
                            <p class="nageeb-type-body-sm nageeb-text-muted">غداً 10:00 · 12 سؤالًا · 30 دقيقة</p>
                        </div>
                        <x-button variant="outline" size="sm">الاستعداد</x-button>
                    </div>
                </div>
            </div>

            <h3 class="nageeb-type-h4 mb-4">نظام الاختبارات</h3>
            <div class="grid gap-6 xl:grid-cols-2 mb-12">
                <x-exam-session />
                <article class="nageeb-file-exam">
                    <div class="nageeb-media rounded-md aspect-[4/3]">
                        <x-nageeb-img path="exams/exam-thumbnail.png" alt="ورقة اختبار مرفقة" />
                    </div>
                    <div>
                        <x-badge variant="info">اختبار مرفقي</x-badge>
                        <h3 class="nageeb-type-h4 mt-3">ورقة امتحان الكيمياء — الفصل الأول</h3>
                        <p class="nageeb-type-body-sm nageeb-text-muted mt-2">ملف محمي للمشتركين في خطة أ. سامي. يمكن العرض أو التنزيل حسب إعداد المعلّم.</p>
                        <ul class="nageeb-plan-card__list mt-4">
                            <li>صيغة PDF · محتوى محمي</li>
                            <li>متاح بعد تفعيل الاشتراك</li>
                            <li>لا يُعرض مسار التخزين</li>
                        </ul>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <x-button variant="primary" size="sm">عرض الورقة</x-button>
                            <x-button variant="outline" size="sm">تنزيل</x-button>
                        </div>
                    </div>
                </article>
            </div>

            <h3 class="nageeb-type-h4 mb-4">لوحة المعلّم</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">الأرباح هذا الشهر</p>
                    <p class="nageeb-stat__value tabular-nums">4,860 ₪</p>
                </div>
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">الطلاب</p>
                    <p class="nageeb-stat__value tabular-nums">1,250</p>
                </div>
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">المواد المنشورة</p>
                    <p class="nageeb-stat__value tabular-nums">4</p>
                </div>
                <div class="nageeb-stat-tile">
                    <p class="nageeb-stat__label">اشتراكات نشطة</p>
                    <p class="nageeb-stat__value tabular-nums">312</p>
                </div>
            </div>
            <div class="grid gap-5 lg:grid-cols-2">
                <x-card title="أداء مادة العربية" subtitle="الفصل الأول · الصف الحادي عشر">
                    <x-progress :value="78" label="متوسط إكمال الدروس" class="mb-4" />
                    <x-progress :value="64" label="تسليم اختبار النحو" />
                    <p class="nageeb-type-caption mt-4">اختبار قادم: الأحد · 86 طالبًا لم يبدؤوا بعد</p>
                </x-card>
                <x-card title="نشاط حديث">
                    <ul class="space-y-3 nageeb-type-body-sm">
                        <li>سارة خالد أكملت درس المفعول به</li>
                        <li>يوسف منصور اشترك في خطة الفيزياء — الضفة</li>
                        <li>نور تيسير سلّمت اختبار الرياضيات بدرجة 91%</li>
                    </ul>
                </x-card>
            </div>
        </x-ds-section>

        {{-- 9. Navigation --}}
        <x-ds-section id="navigation" index="09" title="التنقّل" lede="شريط عام هادئ، وقوائم جانبية للطالب والمعلّم تدعم العربية من اليمين دون أن تشبه لوحة إدارة.">
            <div class="nageeb-card mb-6 !p-0 overflow-hidden">
                <div class="nageeb-public-nav">
                    <span class="nageeb-public-nav__brand"><span class="nageeb-mark">ن</span> نجيب</span>
                    <div class="nageeb-public-nav__links">
                        <a href="#" aria-current="page">المواد</a>
                        <a href="#">المعلمون</a>
                        <a href="#">دخول</a>
                    </div>
                    <x-button variant="primary" size="sm">إنشاء حساب</x-button>
                </div>
            </div>

            <x-breadcrumbs :items="[['label' => 'موادي', 'href' => '#'], ['label' => 'العربية', 'href' => '#'], ['label' => 'المفعول به']]" class="mb-6" />

            <div class="grid gap-6 lg:grid-cols-2">
                <nav class="nageeb-sidebar" aria-label="قائمة الطالب">
                    <p class="nageeb-public-nav__brand px-2 pb-3"><span class="nageeb-mark">ن</span> نجيب</p>
                    <a href="#" aria-current="page">لوحتي</a>
                    <a href="#">موادي</a>
                    <a href="#">الاختبارات</a>
                    <a href="#">إعدادات الحساب</a>
                </nav>
                <nav class="nageeb-sidebar" aria-label="قائمة المعلّم">
                    <p class="nageeb-public-nav__brand px-2 pb-3"><span class="nageeb-mark">ن</span> مساحة المعلّم</p>
                    <a href="#" aria-current="page">الرئيسية</a>
                    <p class="nageeb-sidebar__label">التعليم</p>
                    <a href="#">المواد</a>
                    <a href="#">الاختبارات</a>
                    <p class="nageeb-sidebar__label">المبيعات</p>
                    <a href="#">خطط الوصول</a>
                    <a href="#">الطلبات</a>
                </nav>
            </div>
        </x-ds-section>

        {{-- 10. Responsive --}}
        <x-ds-section id="responsive" index="10" title="المعاينة المتجاوبة" lede="النظام يُبنى من الشاشة الضيقة أولًا. العربية تبقى من اليمين على الجوال والحاسوب.">
            <div class="grid gap-6 xl:grid-cols-2 items-start">
                <div class="nageeb-device" style="max-width: 23.4375rem">
                    <div class="nageeb-device__bar">390 × 844</div>
                    <div class="p-4 space-y-3 bg-background">
                        <x-course-card
                            variant="marketplace"
                            title="اللغة العربية"
                            teacher="أ. محمود طارق"
                            avatar="teachers/mahmoud.png"
                            image="courses/arabic.png"
                            grade="الحادي عشر"
                            progress="72"
                        />
                        <x-button variant="primary" class="w-full">متابعة الدرس</x-button>
                    </div>
                </div>
                <div class="nageeb-device">
                    <div class="nageeb-device__bar">768 — 1440</div>
                    <div class="p-5 bg-background">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-teacher-card
                                variant="showcase"
                                name="أ. ليلى عبد الرحمن"
                                subject="الرياضيات"
                                photo="teachers/layla.png"
                                students="980"
                                courses="3"
                                rating="4.8"
                            />
                            <x-plan-card
                                title="الفصل الأول"
                                course="الرياضيات"
                                grade="الحادي عشر"
                                gaza-price="49"
                                west-bank-price="59"
                                lessons="18 درسًا"
                                :includes="['دروس الفيديو', 'اختبار الوحدة']"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <p class="nageeb-type-caption mt-6">تحقق يدويًا عند: 320 · 375 · 390 · 768 · 1024 · 1280 · 1440. الوضع الليلي غير مدعوم.</p>
        </x-ds-section>
    </main>
</div>
@endsection
