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
            <h2 class="nageeb-heading-1">مرحبًا، {{ $user->name }} <span aria-hidden="true">👋</span></h2>
            <p class="nageeb-text-muted mt-2">إليك ملخص نشاطك التعليمي اليوم.</p>
        </div>
        <x-button href="{{ route('teacher.courses.create') }}" size="lg">+ إضافة مادة</x-button>
    </header>

    @if ($courses->isEmpty())
        <x-card class="py-12 sm:py-16 text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-xl bg-primary-muted text-primary mb-5">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke-width="1.6"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20" stroke-width="1.6"/></svg>
            </span>
            <h3 class="nageeb-heading-2">ابدأ ببناء أول مادة تعليمية لك.</h3>
            <p class="nageeb-text-muted mt-2 mb-6">أضف المحتوى، نظّم الدروس، وابدأ باستقبال طلابك.</p>
            <x-button href="{{ route('teacher.courses.create') }}">+ إضافة مادة</x-button>
        </x-card>
    @else
        <section aria-labelledby="overview-title" class="mb-9">
            <h3 id="overview-title" class="sr-only">ملخص الأداء</h3>
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
                @foreach ([
                    ['label' => 'عدد الطلاب', 'value' => $activeStudentsCount, 'change' => $activeStudentsChange, 'icon' => 'users'],
                    ['label' => 'المواد المباشرة', 'value' => $liveCoursesCount, 'change' => $liveCoursesChange, 'icon' => 'book'],
                    ['label' => 'الاشتراكات النشطة', 'value' => $dashboardCoursePerformance->sum('active_subscriptions'), 'change' => null, 'icon' => 'subscription'],
                    ['label' => 'إيرادات الشهر', 'value' => number_format($currentMonthEarnings, 0).' ₪', 'change' => $earningsChange, 'icon' => 'revenue'],
                ] as $stat)
                    <x-card class="min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-5">
                            <span class="grid size-10 place-items-center rounded-lg bg-primary-muted text-primary">
                                @if ($stat['icon'] === 'users')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-width="1.7"/></svg>
                                @elseif ($stat['icon'] === 'book')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14Z" stroke-width="1.7"/></svg>
                                @elseif ($stat['icon'] === 'subscription')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.7"/><path d="M3 10h18M7 15h3" stroke-width="1.7"/></svg>
                                @else
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="1.7"/></svg>
                                @endif
                            </span>
                            @if ($stat['change'] && $stat['change']['direction'] !== 'flat')
                                <span @class([
                                    'text-xs font-mono',
                                    'text-success' => $stat['change']['direction'] === 'up',
                                    'text-danger' => $stat['change']['direction'] === 'down',
                                ])>
                                    {{ $stat['change']['direction'] === 'up' ? '↑' : '↓' }} {{ $stat['change']['percent'] }}%
                                </span>
                            @endif
                        </div>
                        <strong class="block text-2xl sm:text-3xl font-mono font-semibold truncate">{{ $stat['value'] }}</strong>
                        <span class="block text-xs sm:text-sm nageeb-text-muted mt-1">{{ $stat['label'] }}</span>
                    </x-card>
                @endforeach
            </div>
        </section>

        <div class="grid xl:grid-cols-[minmax(0,1fr)_22rem] gap-6 mb-9">
            <section aria-labelledby="performance-title">
                <div class="flex items-end justify-between gap-4 mb-4">
                    <div>
                        <h3 id="performance-title" class="nageeb-heading-2">أداء المواد</h3>
                        <p class="nageeb-text-muted text-sm mt-1">ملخص حقيقي للالتحاق والتقدم والإيرادات.</p>
                    </div>
                    <a href="{{ route('teacher.courses.index') }}" class="text-sm font-semibold">عرض الكل</a>
                </div>
                <div class="grid gap-3">
                    @foreach ($dashboardCoursePerformance->take(5) as $row)
                        @php($course = $row['course'])
                        <x-card class="grid sm:grid-cols-[4.75rem_minmax(0,1fr)_auto] items-center gap-4" variant="flat">
                            <div class="aspect-[4/3] rounded-lg overflow-hidden bg-primary-muted">
                                @if ($course->coverUrl())
                                    <img src="{{ $course->coverUrl() }}" alt="" class="size-full object-cover">
                                @else
                                    <span class="grid size-full place-items-center text-primary font-bold">ن</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="font-bold truncate">{{ $course->title }}</h4>
                                    <x-badge variant="{{ $course->status->value === 'live' ? 'success' : 'warning' }}">{{ $course->status->label() }}</x-badge>
                                </div>
                                <p class="nageeb-caption mt-1">{{ $course->grade_level?->label() ?? 'كل الصفوف' }}</p>
                                <div class="grid grid-cols-3 gap-2 mt-4 text-xs">
                                    <span><strong class="font-mono text-sm">{{ $row['students'] }}</strong><span class="nageeb-text-muted block">طالب</span></span>
                                    <span><strong class="font-mono text-sm">{{ $row['active_subscriptions'] }}</strong><span class="nageeb-text-muted block">اشتراك</span></span>
                                    <span><strong class="font-mono text-sm">{{ number_format($row['revenue'], 0) }} ₪</strong><span class="nageeb-text-muted block">إيراد</span></span>
                                </div>
                                <x-progress :value="$row['completion']" label="التقدم التقديري" class="mt-4" />
                            </div>
                            <x-button href="{{ route('teacher.courses.content', $course) }}" variant="outline" size="sm">إدارة المادة</x-button>
                        </x-card>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-6">
                <x-card title="يتطلب انتباهك">
                    <div class="divide-y divide-border">
                        @foreach ([
                            ['count' => $pendingRequestsCount, 'label' => 'طلبات اشتراك معلقة', 'href' => route('teacher.subscription-requests.index', ['status' => 'pending'])],
                            ['count' => $draftCoursesCount, 'label' => 'مواد ما زالت مسودة', 'href' => route('teacher.courses.index')],
                            ['count' => $incompleteCoursesCount, 'label' => 'مواد إعدادها غير مكتمل', 'href' => route('teacher.courses.index')],
                            ['count' => $lessonsWithoutContentCount, 'label' => 'دروس بلا محتوى', 'href' => route('teacher.courses.index')],
                            ['count' => $recentUnansweredQuestions->count(), 'label' => 'أسئلة تنتظر الرد', 'href' => route('teacher.interactions.index')],
                        ] as $item)
                            <a href="{{ $item['href'] }}" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0 text-text hover:text-primary">
                                <span class="text-sm">{{ $item['label'] }}</span>
                                <span @class(['font-mono text-sm font-semibold', 'text-danger' => $item['count'] > 0, 'nageeb-text-dim' => $item['count'] === 0])>{{ $item['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-card>

                <x-card title="إجراءات سريعة">
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('teacher.courses.create') }}" class="p-3 rounded-md bg-primary-muted text-primary hover:bg-primary hover:text-white transition-colors text-sm font-semibold">+ إضافة مادة</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة وحدة</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة درس</a>
                        <a href="{{ route('teacher.courses.index') }}" class="p-3 rounded-md bg-surface-muted text-text hover:bg-primary-muted hover:text-primary transition-colors text-sm font-semibold">+ إضافة خطة وصول</a>
                        <a href="{{ route('teacher.courses.index') }}" class="col-span-2 p-3 rounded-md border border-border text-text hover:border-primary hover:text-primary transition-colors text-sm font-semibold">↑ رفع فيديو</a>
                    </div>
                </x-card>
            </aside>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <x-card title="النشاط الأخير" subtitle="آخر ما حدث عبر موادك وطلابك">
                @forelse ($recentActivity as $activity)
                    <a href="{{ $activity['url'] }}" class="flex gap-3 py-3 border-b border-border last:border-0 last:pb-0 first:pt-0 text-text hover:text-primary">
                        <span class="mt-1 size-2 rounded-full bg-primary shrink-0"></span>
                        <span class="min-w-0 flex-1">
                            <span class="font-semibold text-sm block">{{ $activity['title'] }}</span>
                            <span class="nageeb-text-muted text-xs block truncate mt-0.5">{{ $activity['description'] }}</span>
                        </span>
                        <time class="nageeb-caption shrink-0">{{ $activity['at']?->locale('ar')->diffForHumans() }}</time>
                    </a>
                @empty
                    <x-empty-state title="لا يوجد نشاط حديث." />
                @endforelse
            </x-card>

            <x-card title="طلاب يحتاجون متابعة" subtitle="لم يسجّلوا نشاطاً خلال آخر 10 أيام">
                @forelse ($atRiskStudents as $row)
                    <div class="flex items-center justify-between gap-3 py-3 border-b border-border last:border-0 first:pt-0 last:pb-0">
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
                    <x-empty-state title="لا يوجد طلاب يحتاجون متابعة.">كل الطلاب النشطين يسيرون بشكل جيد.</x-empty-state>
                @endforelse
            </x-card>
        </div>
    @endif
</x-dashboard-layout>
@endsection
