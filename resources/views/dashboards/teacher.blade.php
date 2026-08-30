@extends('layouts.app')

@section('title', 'الرئيسية — نجيب')

@section('content')
<x-dashboard-layout title="الرئيسية" role-label="المعلّم" active-menu="dashboard">
    @if (! $profile?->is_verified)
        <div class="nageeb-alert nageeb-alert--warning mb-6">حسابك قيد المراجعة. يمكنك تجهيز موادك حتى اكتمال التوثيق.</div>
    @endif

    <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between mb-8">
        <div>
            <p class="nageeb-caption mb-2">{{ now()->locale('ar')->translatedFormat('l، j F') }}</p>
            <h2 class="nageeb-heading-1">مرحبًا، {{ $user->name }}</h2>
            <p class="nageeb-text-muted mt-2">إليك ملخص نشاطك التعليمي اليوم.</p>
        </div>
        <x-button href="{{ route('teacher.courses.create') }}" size="lg">+ إضافة مادة</x-button>
    </header>

    @if ($courses->isEmpty())
        <x-empty-state title="ابدأ ببناء أول مادة تعليمية لك." action-href="{{ route('teacher.courses.create') }}" action-label="+ إضافة مادة">
            أضف المحتوى، نظّم الدروس، وابدأ باستقبال طلابك.
        </x-empty-state>
    @else
        <x-reveal class="mb-9" aria-labelledby="overview-title">
            <section aria-labelledby="overview-title">
                <h3 id="overview-title" class="sr-only">ملخص الأداء</h3>
                <div class="nageeb-kpi-strip">
                    <x-dashboard-stat label="إيرادات الشهر" :value="number_format($currentMonthEarnings, 0).' ₪'" :change="$earningsChange" />
                    <x-dashboard-stat label="عدد الطلاب" :value="$activeStudentsCount" :change="$activeStudentsChange" />
                    <x-dashboard-stat label="المواد المباشرة" :value="$liveCoursesCount" :change="$liveCoursesChange" />
                    <x-dashboard-stat label="الاشتراكات النشطة" :value="$dashboardCoursePerformance->sum('active_subscriptions')" />
                </div>
            </section>
        </x-reveal>

        @if (! empty($chartPayload['growth']['labels']))
            <div class="grid lg:grid-cols-[minmax(0,1.4fr)_16rem] gap-6 mb-9">
                <div class="nageeb-chart">
                    <p class="nageeb-type-caption mb-3">نمو الطلاب خلال ستة أشهر</p>
                    <canvas id="nageeb-growth-chart" height="220"></canvas>
                </div>
                @if (! empty($weeklyPulse))
                    <div>
                        <p class="nageeb-type-caption mb-3">نبض الأسبوع</p>
                        <div class="nageeb-pulse" aria-label="نشاط الأيام السبعة الأخيرة">
                            @foreach ($weeklyPulse as $day)
                                <span data-level="{{ $day['level'] }}" title="{{ $day['label'] }}"></span>
                            @endforeach
                        </div>
                        <p class="nageeb-caption mt-3">ردود، دروس، ومراجعة طلبات.</p>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid xl:grid-cols-[minmax(0,1fr)_22rem] gap-8 mb-9">
            <section aria-labelledby="performance-title">
                <div class="flex items-end justify-between gap-4 mb-4">
                    <div>
                        <h3 id="performance-title" class="nageeb-heading-2">أداء المواد</h3>
                        <p class="nageeb-text-muted text-sm mt-1">ملخص حقيقي للالتحاق والتقدم والإيرادات.</p>
                    </div>
                    <a href="{{ route('teacher.courses.index') }}" class="text-sm font-semibold">عرض الكل</a>
                </div>
                <div class="nageeb-list">
                    @foreach ($dashboardCoursePerformance->take(5) as $row)
                        @php($course = $row['course'])
                        <article class="grid sm:grid-cols-[4.75rem_minmax(0,1fr)_auto] items-center gap-4">
                            <div class="aspect-[4/3] rounded-lg overflow-hidden bg-primary-muted nageeb-media">
                                <img src="{{ \App\Support\NageebVisual::courseCover($course) }}" alt="" class="size-full object-cover" loading="lazy">
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="font-bold truncate">{{ $course->title }}</h4>
                                    <x-badge variant="{{ $course->status->value === 'live' ? 'success' : 'warning' }}">{{ $course->status->label() }}</x-badge>
                                </div>
                                <p class="nageeb-caption mt-1">{{ $course->grade_level?->label() ?? 'كل الصفوف' }}</p>
                                <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                                    <span><strong class="font-mono text-sm">{{ $row['students'] }}</strong><span class="nageeb-text-muted block">طالب</span></span>
                                    <span><strong class="font-mono text-sm">{{ $row['active_subscriptions'] }}</strong><span class="nageeb-text-muted block">اشتراك</span></span>
                                    <span><strong class="font-mono text-sm">{{ number_format($row['revenue'], 0) }} ₪</strong><span class="nageeb-text-muted block">إيراد</span></span>
                                </div>
                                <x-progress :value="$row['completion']" label="التقدم التقديري" class="mt-3" />
                            </div>
                            <x-button href="{{ route('teacher.courses.content', $course) }}" variant="outline" size="sm">إدارة المادة</x-button>
                        </article>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-8">
                <section>
                    <h3 class="nageeb-heading-3 mb-1">يتطلب انتباهك</h3>
                    <p class="nageeb-text-muted text-sm mb-3">عناصر تحتاج إجراءً منك قبل أن تكتمل التجربة التعليمية.</p>
                    <div class="nageeb-list">
                        @foreach ([
                            ['count' => $pendingRequestsCount, 'label' => 'طلبات اشتراك معلقة', 'href' => route('teacher.subscription-requests.index', ['status' => 'pending'])],
                            ['count' => $draftCoursesCount, 'label' => 'مواد ما زالت مسودة', 'href' => route('teacher.courses.index')],
                            ['count' => $incompleteCoursesCount, 'label' => 'مواد إعدادها غير مكتمل', 'href' => route('teacher.courses.index')],
                            ['count' => $lessonsWithoutContentCount, 'label' => 'دروس بلا محتوى', 'href' => route('teacher.courses.index')],
                            ['count' => $recentUnansweredQuestions->count(), 'label' => 'أسئلة تنتظر الرد', 'href' => route('teacher.interactions.index')],
                        ] as $item)
                            <a href="{{ $item['href'] }}" class="flex items-center justify-between gap-3 text-text hover:text-primary">
                                <span class="text-sm">{{ $item['label'] }}</span>
                                <span @class(['font-mono text-sm font-semibold', 'text-danger' => $item['count'] > 0, 'nageeb-text-dim' => $item['count'] === 0])>{{ $item['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section>
                    <h3 class="nageeb-heading-3 mb-3">إجراءات سريعة</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('teacher.courses.create') }}" class="p-3 rounded-md bg-primary-muted text-primary hover:bg-primary hover:text-white transition-colors text-sm font-semibold">+ إضافة مادة</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة وحدة</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة درس</a>
                        <a href="{{ route('teacher.exams.create') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة اختبار</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة خطة وصول</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md border border-border text-text hover:border-primary hover:text-primary transition-colors text-sm font-semibold">↑ رفع فيديو</a>
                    </div>
                </section>
            </aside>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <section>
                <h3 class="nageeb-heading-3 mb-1">النشاط الأخير</h3>
                <p class="nageeb-text-muted text-sm mb-3">آخر ما حدث عبر موادك وطلابك</p>
                <div class="nageeb-list">
                    @forelse ($recentActivity as $activity)
                        <x-activity-item
                            :href="$activity['url']"
                            :title="$activity['title']"
                            :description="$activity['description']"
                            :time="$activity['at']?->locale('ar')->diffForHumans()"
                        />
                    @empty
                        <div>
                            <x-empty-state title="لا يوجد نشاط حديث." />
                        </div>
                    @endforelse
                </div>
            </section>

            <section>
                <h3 class="nageeb-heading-3 mb-1">طلاب يحتاجون متابعة</h3>
                <p class="nageeb-text-muted text-sm mb-3">لم يسجّلوا نشاطاً خلال آخر 10 أيام</p>
                <div class="nageeb-list">
                    @forelse ($atRiskStudents as $row)
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm truncate">{{ $row['student_name'] }}</p>
                                <p class="nageeb-caption truncate">{{ $row['course_title'] }} · {{ $row['inactive_days'] }} يوم</p>
                            </div>
                            <form method="POST" action="{{ route('teacher.dashboard.remind', $row['student_id']) }}">
                                @csrf
                                <input type="hidden" name="course_id" value="{{ $row['course_id'] }}">
                                <x-button type="submit" variant="outline" size="sm">إرسال تذكير</x-button>
                            </form>
                        </div>
                    @empty
                        <div>
                            <x-empty-state title="لا يوجد طلاب يحتاجون متابعة.">كل الطلاب النشطين يسيرون بشكل جيد.</x-empty-state>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    @endif
</x-dashboard-layout>
@endsection

@push('scripts')
@if (! empty($chartPayload['growth']['labels']))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('nageeb-growth-chart');
        if (! canvas || ! window.Chart) {
            return;
        }

        const payload = @json($chartPayload['growth']);
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        new window.Chart(canvas, {
            type: 'line',
            data: {
                labels: payload.labels,
                datasets: [{
                    label: 'طلاب جدد',
                    data: payload.values,
                    borderColor: '#9A5B43',
                    backgroundColor: 'rgba(154, 91, 67, 0.12)',
                    fill: true,
                    tension: reduced ? 0 : 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#C8A98A',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#E9DED4' } },
                },
                animation: reduced ? false : { duration: 700 },
            },
        });
    });
</script>
@endif
@endpush
