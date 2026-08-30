@extends('layouts.app')

@section('title', 'نجيب — تعلّم بوضوح، وتقدّم بثقة')

@section('content')
@php
    $featured = $heroCourses->first();
    $catalogCourses = $heroCourses->concat($exploreCourses);
    $subjectCount = static fn (string $needle) => $catalogCourses->filter(
        fn ($course) => str_contains((string) $course->title, $needle)
    )->count();
    $arabicCount = $subjectCount('عربي');
    $mathCount = $subjectCount('رياض');
    $physicsCount = $subjectCount('فيزياء');
    $chemistryCount = $subjectCount('كيمياء');
    $englishCount = $catalogCourses->filter(
        fn ($course) => str_contains((string) $course->title, 'إنجل') || str_contains((string) $course->title, 'انجليز')
    )->count();
    $csCount = $catalogCourses->filter(
        fn ($course) => str_contains((string) $course->title, 'حاسوب') || str_contains((string) $course->title, 'برمجة')
    )->count();
    $shownCount = static fn (int $n) => $n.' مواد معروضة';
    $shownCourses = $catalogCourses->take(3);
@endphp

<div class="nageeb-page min-h-screen">
    <x-site-header current="home" />

    <section class="nageeb-hero-band">
        <span class="nageeb-orb nageeb-orb--one" aria-hidden="true"></span>
        <span class="nageeb-orb nageeb-orb--two" aria-hidden="true"></span>
        <div class="nageeb-container nageeb-hero">
            <div class="nageeb-hero__copy">
                <p class="nageeb-kicker">منصة تعليمية متكاملة</p>
                <h1 class="nageeb-type-display mt-4">
                    <span class="nageeb-hero-line">تعلّم بوضوح،</span>
                    <span class="nageeb-hero-line">وتقدّم بثقة.</span>
                </h1>
                <p class="nageeb-hero-lede nageeb-type-body-lg nageeb-text-muted mt-5">
                    كل ما تحتاجه لرحلتك التعليمية في مكان واحد.
                    اكتشف مواد تعليمية منظمة، تعلّم من معلمين متخصصين،
                    وتابع تقدمك بتجربة صُممت لتجعل التعلم أكثر وضوحًا وفاعلية.
                </p>
                <div class="nageeb-hero__actions">
                    @guest
                        <x-button variant="primary" size="lg" href="{{ route('register.student') }}">ابدأ رحلتك التعليمية</x-button>
                    @else
                        <x-button variant="primary" size="lg" href="{{ auth()->user()->dashboardRoute() }}">إلى لوحتي</x-button>
                    @endguest
                    <x-button variant="outline" size="lg" href="{{ route('courses.index') }}">استكشف المواد</x-button>
                </div>
            </div>

            <div class="nageeb-hero__stage">
                <div class="nageeb-hero__photo nageeb-media">
                    <x-nageeb-img path="hero/hero-student-editorial.png" alt="طالب يدرس في بيئة تعليمية هادئة" eager />
                </div>

                <aside class="nageeb-hero__float nageeb-hero__float--progress" aria-hidden="true">
                    <p class="nageeb-type-caption mb-1">نموذج توضيحي</p>
                    <p class="nageeb-type-label text-text mb-2">تقدمك في المادة</p>
                    <x-progress :value="72" label="العربية" />
                </aside>

                @if ($featured)
                    <article class="nageeb-hero__float nageeb-hero__float--course">
                        <div class="flex gap-3">
                            <img src="{{ \App\Support\NageebVisual::courseCover($featured) }}" alt="" class="size-14 rounded-md object-cover">
                            <div class="min-w-0">
                                <p class="nageeb-type-caption">مادة منشورة</p>
                                <p class="nageeb-type-label text-text line-clamp-2">{{ $featured->title }}</p>
                                <p class="nageeb-type-caption mt-1">{{ $featured->teacher->name }}</p>
                            </div>
                        </div>
                    </article>
                @else
                    <aside class="nageeb-hero__float nageeb-hero__float--course" aria-hidden="true">
                        <p class="nageeb-type-caption">نموذج توضيحي</p>
                        <p class="nageeb-type-label text-text mt-1">الدرس التالي</p>
                    </aside>
                @endif

                @if ($teachers->isNotEmpty())
                    <aside class="nageeb-hero__float nageeb-hero__float--teacher">
                        <img src="{{ \App\Support\NageebVisual::teacherPhoto($teachers->first()) }}" alt="" class="nageeb-avatar">
                        <div class="min-w-0">
                            <p class="nageeb-type-label text-text truncate">{{ $teachers->first()->name }}</p>
                            <p class="nageeb-type-caption">{{ $teachers->first()->teacherProfile?->specialization ?: 'معلم' }}</p>
                        </div>
                    </aside>
                @endif
            </div>
        </div>
    </section>

    <section class="nageeb-container nageeb-band">
        <x-reveal>
            <x-section-header title="التعليم الذي يجمع كل ما تحتاجه" lede="من المحتوى إلى المتابعة، صُممت نجِيب لتمنح الطالب والمعلم تجربة تعليمية أكثر تنظيمًا ووضوحًا." />
        </x-reveal>
        <x-reveal stagger class="nageeb-value-list">
            <article class="nageeb-value-item nageeb-reveal-item">
                <span class="nageeb-value-item__icon nageeb-value-item__icon--brown" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14Z" stroke-width="1.7"/></svg>
                </span>
                <div>
                    <h3 class="nageeb-type-h3">محتوى منظم</h3>
                    <p class="nageeb-type-body-sm nageeb-text-muted mt-1">مواد، وحدات، ودروس يبنيها المعلّم حسب مساره.</p>
                </div>
            </article>
            <article class="nageeb-value-item nageeb-reveal-item">
                <span class="nageeb-value-item__icon nageeb-value-item__icon--terra" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke-width="1.7"/><circle cx="9" cy="7" r="4" stroke-width="1.7"/></svg>
                </span>
                <div>
                    <h3 class="nageeb-type-h3">معلمون متخصصون</h3>
                    <p class="nageeb-type-body-sm nageeb-text-muted mt-1">ملفات موثّقة ومواد حيّة من أصحابها مباشرة.</p>
                </div>
            </article>
            <article class="nageeb-value-item nageeb-reveal-item">
                <span class="nageeb-value-item__icon nageeb-value-item__icon--sand" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="14" rx="2" stroke-width="1.7"/><path d="M8 21h8M12 18v3" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <h3 class="nageeb-type-h3">تعلم في مكان واحد</h3>
                    <p class="nageeb-type-body-sm nageeb-text-muted mt-1">الدروس، المرفقات، والاختبارات داخل المنصة.</p>
                </div>
            </article>
            <article class="nageeb-value-item nageeb-reveal-item">
                <span class="nageeb-value-item__icon nageeb-value-item__icon--cream" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19h16M6 16V8m6 8V5m6 11v-6" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <h3 class="nageeb-type-h3">متابعة أوضح للتقدم</h3>
                    <p class="nageeb-type-body-sm nageeb-text-muted mt-1">لوحات للطالب والمعلّم تعرض النشاط الحقيقي.</p>
                </div>
            </article>
        </x-reveal>
        <x-reveal class="mt-8">
            <div class="nageeb-stats-row">
                <x-stat-item label="طالب مسجّل" :value="'+'.number_format($studentsCount)" :count="'+'.$studentsCount" />
                <x-stat-item label="مادة منشورة" :value="$liveCoursesCount" :count="$liveCoursesCount" />
                <x-stat-item label="معلّم على المنصة" :value="$teachersCount" :count="$teachersCount" />
            </div>
        </x-reveal>
    </section>

    <section class="nageeb-band nageeb-band--sage">
        <div class="nageeb-container">
            <x-reveal>
                <x-section-header kicker="المجالات" title="استكشف مجالات التعلم" lede="اختر المجال الذي يناسب أهدافك وابدأ التعلم بالطريقة التي تناسبك." />
            </x-reveal>
            <x-reveal stagger class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                <x-subject-card class="nageeb-reveal-item" label="لغة عربية" image="courses/arabic.png" icon="book" :count="$shownCount($arabicCount)" href="{{ route('courses.index') }}" />
                <x-subject-card class="nageeb-reveal-item" label="رياضيات" image="courses/mathematics.png" icon="math" :count="$shownCount($mathCount)" href="{{ route('courses.index') }}" />
                <x-subject-card class="nageeb-reveal-item" label="فيزياء" image="courses/physics.png" icon="physics" :count="$shownCount($physicsCount)" href="{{ route('courses.index') }}" />
                <x-subject-card class="nageeb-reveal-item" label="كيمياء" image="courses/chemistry.png" icon="chemistry" :count="$shownCount($chemistryCount)" href="{{ route('courses.index') }}" />
                <x-subject-card class="nageeb-reveal-item" label="لغة إنجليزية" image="courses/english.png" icon="english" :count="$shownCount($englishCount)" href="{{ route('courses.index') }}" />
                <x-subject-card class="nageeb-reveal-item" label="حاسوب" image="courses/computer.png" icon="computer" :count="$shownCount($csCount)" href="{{ route('courses.index') }}" />
            </x-reveal>
        </div>
    </section>

    <section class="nageeb-container nageeb-band">
        <x-reveal class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <x-section-header kicker="المواد" title="مواد تعليمية تستحق وقتك" lede="اكتشف محتوى تعليميًا منظمًا يساعدك على الفهم والتطبيق والتقدم." class="mb-0" />
            <a href="{{ route('courses.index') }}" class="text-sm font-semibold">كل المواد المنشورة</a>
        </x-reveal>

        @if ($shownCourses->isEmpty())
            <x-empty-state title="لا توجد مواد بعد" action-href="{{ route('courses.index') }}" action-label="فتح الكتالوج">
                ابدأ من الكتالوج عندما ينشر المعلمون موادهم الحيّة.
            </x-empty-state>
        @elseif ($shownCourses->count() === 1)
            @php($course = $shownCourses->first())
            <x-reveal class="nageeb-featured-material">
                <x-course-card
                    variant="featured"
                    :title="$course->title"
                    :teacher="$course->teacher->name"
                    :image="\App\Support\NageebVisual::courseCover($course)"
                    :avatar="\App\Support\NageebVisual::teacherPhoto($course->teacher)"
                    :subject="\App\Support\NageebVisual::subjectLabel($course->title)"
                    :grade="$course->grade_level?->label()"
                    :students="$course->enrollments_count ? number_format($course->enrollments_count).' طالب' : null"
                    region="غزة والضفة"
                    :price="$course->is_free ? 'مجاني' : ($course->reference_price ? 'من '.number_format((float) $course->reference_price).' ₪' : null)"
                    :href="$course->is_free ? route('student.my-courses.show', $course) : route('courses.subscribe', $course)"
                    :cta="$course->is_free ? 'دخول مجاني' : 'عرض المادة'"
                    badge="مادة منشورة"
                />
                <div>
                    <p class="nageeb-kicker">مادة حيّة الآن</p>
                    <h3 class="nageeb-type-h2 mt-2">{{ $course->title }}</h3>
                    <p class="nageeb-type-body nageeb-text-muted mt-3">
                        يقدّمها {{ $course->teacher->name }}{{ $course->grade_level ? ' — '.$course->grade_level->label() : '' }}.
                        هذه المادة المنشورة حاليًا على المنصة — ستظهر مواد إضافية هنا عند نشرها.
                    </p>
                    <div class="mt-5">
                        <x-button variant="primary" href="{{ $course->is_free ? route('student.my-courses.show', $course) : route('courses.subscribe', $course) }}">
                            {{ $course->is_free ? 'دخول مجاني' : 'عرض المادة' }}
                        </x-button>
                    </div>
                </div>
            </x-reveal>
        @else
            <x-reveal stagger class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($shownCourses as $course)
                    <div class="nageeb-reveal-item">
                        <x-course-card
                            variant="marketplace"
                            :title="$course->title"
                            :teacher="$course->teacher->name"
                            :image="\App\Support\NageebVisual::courseCover($course, $loop->index)"
                            :avatar="\App\Support\NageebVisual::teacherPhoto($course->teacher)"
                            :subject="\App\Support\NageebVisual::subjectLabel($course->title)"
                            :grade="$course->grade_level?->label()"
                            :students="$course->enrollments_count ? number_format($course->enrollments_count).' طالب' : null"
                            region="غزة والضفة"
                            :price="$course->is_free ? 'مجاني' : ($course->reference_price ? 'من '.number_format((float) $course->reference_price).' ₪' : null)"
                            :href="$course->is_free ? route('student.my-courses.show', $course) : route('courses.subscribe', $course)"
                            :cta="$course->is_free ? 'دخول مجاني' : 'عرض المادة'"
                            :badge="$loop->first ? 'الأحدث' : null"
                        />
                    </div>
                @endforeach
            </x-reveal>
        @endif
    </section>

    <section class="nageeb-band nageeb-band--sage">
        <div class="nageeb-container">
            <x-reveal class="nageeb-trust">
                <div class="nageeb-media rounded-[1.25rem] min-h-[14rem] order-2 md:order-none">
                    <x-nageeb-img path="process/classroom.png" alt="بيئة تعليمية هادئة" />
                </div>
                <div>
                    <p class="nageeb-kicker">لماذا نجيب</p>
                    <h2 class="nageeb-type-h2 mt-2">تجربة تعليمية صُممت حول احتياجك</h2>
                    <ul class="mt-6 space-y-4 nageeb-type-body nageeb-text-muted">
                        <li>اشترك في خطة المعلّم حسب منطقتك، ثم ادخل دروسك المحمية.</li>
                        <li>تابع الوحدات والدروس والاختبارات من مساحة واحدة.</li>
                        <li>يدير المعلّم مواده وطلابه وطلباته من لوحة تحكم واحدة.</li>
                    </ul>
                </div>
            </x-reveal>
        </div>
    </section>

    <section class="nageeb-container nageeb-band">
        <x-reveal>
            <x-section-header kicker="كيف تعمل المنصة" title="رحلتك التعليمية تبدأ بخطوات بسيطة" />
        </x-reveal>
        <x-reveal stagger class="nageeb-steps">
            <article class="nageeb-step nageeb-reveal-item">
                <div class="nageeb-step__media nageeb-media">
                    <x-nageeb-img path="courses/mathematics.png" alt="اختيار مادة تعليمية" />
                </div>
                <p class="nageeb-step__index">01</p>
                <h3 class="nageeb-type-h3">اختر ما يناسبك</h3>
                <p class="nageeb-type-body-sm nageeb-text-muted mt-2">استكشف المواد التعليمية واختر المحتوى الذي يناسب مستواك.</p>
            </article>
            <article class="nageeb-step nageeb-reveal-item">
                <div class="nageeb-step__media nageeb-media">
                    <x-nageeb-img path="lessons/lesson-thumbnail.png" alt="متابعة درس" />
                </div>
                <p class="nageeb-step__index">02</p>
                <h3 class="nageeb-type-h3">تعلّم وتابع تقدمك</h3>
                <p class="nageeb-type-body-sm nageeb-text-muted mt-2">ادخل الدروس المحمية وراجع محتواك في أي وقت.</p>
            </article>
            <article class="nageeb-step nageeb-reveal-item">
                <div class="nageeb-step__media nageeb-media">
                    <x-nageeb-img path="exams/exam-thumbnail.png" alt="اختبار داخل المادة" />
                </div>
                <p class="nageeb-step__index">03</p>
                <h3 class="nageeb-type-h3">اختبر معرفتك</h3>
                <p class="nageeb-type-body-sm nageeb-text-muted mt-2">اختبارات يفتحها المعلّم من داخل المادة نفسها.</p>
            </article>
        </x-reveal>
    </section>

    <section id="teachers" class="nageeb-band nageeb-band--sage">
        <div class="nageeb-container">
            <x-reveal>
                <x-section-header kicker="المعلمون" title="تعلّم على يد معلمين يصنعون الفرق" lede="خبرات تعليمية متنوعة، ومحتوى يساعدك على التعلم بخطوات أكثر وضوحًا." />
            </x-reveal>

            @if ($teachers->isEmpty())
                <x-empty-state title="لا يوجد معلّمون موثّقون بعد.">
                    تظهر هنا أسماء من وثّقتهم الإدارة ولديهم ملف على المنصة.
                </x-empty-state>
            @elseif ($teachers->count() === 1)
                @php($teacher = $teachers->first())
                <x-reveal>
                    <x-teacher-card
                        variant="featured"
                        :name="$teacher->name"
                        :subject="$teacher->teacherProfile?->specialization"
                        :photo="\App\Support\NageebVisual::teacherPhoto($teacher)"
                        :courses="$teacher->live_courses_count"
                        :bio="$teacher->teacherProfile?->bio"
                        :href="route('teachers.show', $teacher)"
                    />
                </x-reveal>
            @else
                <x-reveal stagger class="grid gap-5 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($teachers->take(3) as $teacher)
                        <div class="nageeb-reveal-item">
                            <x-teacher-card
                                variant="showcase"
                                :name="$teacher->name"
                                :subject="$teacher->teacherProfile?->specialization"
                                :photo="\App\Support\NageebVisual::teacherPhoto($teacher)"
                                :courses="$teacher->live_courses_count"
                                :bio="$teacher->teacherProfile?->bio"
                                :href="route('teachers.show', $teacher)"
                            />
                        </div>
                    @endforeach
                </x-reveal>
            @endif
        </div>
    </section>

    <section class="nageeb-container nageeb-band">
        <x-reveal class="nageeb-trust">
            <div>
                <p class="nageeb-kicker">الثقة</p>
                <h2 class="nageeb-type-h2 mt-2">تجارب تصنع فرقًا</h2>
                <p class="nageeb-type-body nageeb-text-muted mt-3 max-w-lg">
                    لا نعرض شهادات مخترعة. الأرقام أدناه من المنصة اليوم، مع محتوى محمي للمشتركين حسب خطط الوصول.
                </p>
                <div class="nageeb-value-list nageeb-value-list--stack mt-5">
                    <article class="nageeb-value-item">
                        <span class="nageeb-value-item__icon nageeb-value-item__icon--brown" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke-width="1.7"/><circle cx="9" cy="7" r="4" stroke-width="1.7"/></svg>
                        </span>
                        <div>
                            <h3 class="nageeb-type-h3">{{ $teachersCount }} معلّم موثّق</h3>
                            <p class="nageeb-type-body-sm nageeb-text-muted mt-1">ملفات ظاهرة للعامة بعد توثيق الإدارة.</p>
                        </div>
                    </article>
                    <article class="nageeb-value-item">
                        <span class="nageeb-value-item__icon nageeb-value-item__icon--terra" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14Z" stroke-width="1.7"/></svg>
                        </span>
                        <div>
                            <h3 class="nageeb-type-h3">{{ $liveCoursesCount }} مادة حيّة</h3>
                            <p class="nageeb-type-body-sm nageeb-text-muted mt-1">مواد منشورة يمكن استكشافها والاشتراك فيها.</p>
                        </div>
                    </article>
                    <article class="nageeb-value-item">
                        <span class="nageeb-value-item__icon nageeb-value-item__icon--sand" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" stroke-width="1.7"/></svg>
                        </span>
                        <div>
                            <h3 class="nageeb-type-h3">محتوى محمي للمشتركين</h3>
                            <p class="nageeb-type-body-sm nageeb-text-muted mt-1">الدروس والاختبارات تُفتح حسب خطة الوصول ومنطقتك.</p>
                        </div>
                    </article>
                </div>
            </div>
            <div class="nageeb-media rounded-[1.25rem] min-h-[14rem]">
                <x-nageeb-img path="courses/dashboard-cover.png" alt="مساحة التعلّم على نجيب" />
            </div>
        </x-reveal>
    </section>

    <section class="nageeb-container nageeb-band">
        <x-reveal>
            <x-cta-section
                title="جاهز تبدأ رحلتك التعليمية؟"
                lede="اكتشف المواد، تعلّم من خبرات متخصصة، وابنِ طريقك نحو تقدم حقيقي."
                :action-href="auth()->check() ? auth()->user()->dashboardRoute() : route('register.student')"
                :action-label="auth()->check() ? 'إلى لوحتي' : 'ابدأ الآن'"
            >
                <x-button variant="outline" class="nageeb-cta__ghost" href="{{ route('courses.index') }}">استكشف المنصة</x-button>
            </x-cta-section>
        </x-reveal>
    </section>

    <x-site-footer />
</div>
@endsection
