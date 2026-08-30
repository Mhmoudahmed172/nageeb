@extends('layouts.app')

@section('title', $exam->title.' — نجيب')

@section('content')
<x-dashboard-layout title="{{ $exam->title }}" role-label="الطالب" active-menu="exams">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="nageeb-alert nageeb-alert--error mb-6">{{ session('error') }}</div>
    @endif

    <div class="nageeb-card mb-6 overflow-hidden !p-0">
        <div class="nageeb-media aspect-[16/6]">
            <img src="{{ asset('images/nageeb/exams/exam-thumbnail.png') }}" alt="" loading="lazy">
        </div>
        <div class="p-6">
        <p class="nageeb-caption">{{ $exam->course->title }}{{ $exam->lesson ? ' · '.$exam->lesson->title : '' }}</p>
        <h2 class="nageeb-heading-2 mt-1">{{ $exam->title }}</h2>

        @if ($exam->description)
            <p class="text-sm nageeb-text-muted mt-3">{{ $exam->description }}</p>
        @endif

        @if ($exam->isFileExam())
            <p class="nageeb-text-muted text-sm mt-3">ورقة اختبار للتحميل. لا توجد أسئلة تفاعلية داخل المنصة.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @if ($exam->hasPaperFile())
                    <x-button href="{{ $exam->paperUrl() }}">فتح ورقة الاختبار</x-button>
                @else
                    <p class="nageeb-alert nageeb-alert--info w-full">لم يرفع المعلّم ملف الاختبار بعد.</p>
                @endif
            </div>
        @else
            <h3 class="nageeb-title-section mt-6 mb-3">تعليمات الاختبار</h3>
            <ul class="grid gap-2 text-sm">
                <li>عدد الأسئلة: <strong>{{ $exam->questions_count }}</strong></li>
                <li>الدرجة الكاملة: <strong>{{ $totalPoints }}</strong></li>
                <li>درجة النجاح: <strong>{{ (float) $exam->passing_score }}%</strong></li>
                <li>المدة: <strong>{{ $exam->duration_minutes ? $exam->duration_minutes.' دقيقة' : 'بدون وقت محدد' }}</strong></li>
                <li>المحاولات المسموحة: <strong>{{ $exam->max_attempts }}</strong> (استخدمت {{ $attemptsUsed }})</li>
                @if ($exam->duration_minutes)
                    <li class="nageeb-text-muted">يبدأ العدّاد فور الضغط على «بدء الاختبار»، ويُسلَّم الاختبار تلقائيًا عند انتهاء الوقت.</li>
                @endif
                <li class="nageeb-text-muted">تُحفظ إجاباتك أولًا بأول، ويمكنك متابعة المحاولة إذا أُغلقت الصفحة.</li>
            </ul>

            <div class="mt-6 flex flex-wrap gap-2">
                @if ($exam->questions_count === 0)
                    <p class="nageeb-alert nageeb-alert--info w-full">لم يضف المعلّم أسئلة لهذا الاختبار بعد.</p>
                @elseif ($openAttempt)
                    <form method="POST" action="{{ route('student.exams.start', $exam) }}">
                        @csrf
                        <x-button type="submit">متابعة المحاولة الحالية</x-button>
                    </form>
                @elseif ($attemptsUsed >= $exam->max_attempts)
                    <p class="nageeb-alert nageeb-alert--warning w-full">استنفدت عدد المحاولات المسموح بها لهذا الاختبار.</p>
                @else
                    <form method="POST" action="{{ route('student.exams.start', $exam) }}">
                        @csrf
                        <x-button type="submit">بدء الاختبار</x-button>
                    </form>
                @endif
            </div>
        @endif
        </div>
    </div>

    @if ($previousAttempts->isNotEmpty())
        <div class="nageeb-card nageeb-table-wrap">
            <h3 class="nageeb-title-section mb-4">محاولاتك السابقة</h3>
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">المحاولة</th>
                        <th class="py-3 px-2 font-medium">التاريخ</th>
                        <th class="py-3 px-2 font-medium">النتيجة</th>
                        <th class="py-3 px-2 font-medium">الحالة</th>
                        <th class="py-3 px-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($previousAttempts as $attempt)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2">{{ $attempt->attempt_number }}</td>
                            <td class="py-3 px-2">{{ $attempt->submitted_at?->format('Y/m/d H:i') }}</td>
                            <td class="py-3 px-2">
                                {{ $exam->show_results_immediately ? ((float) $attempt->percentage).'%' : 'بانتظار المعلّم' }}
                            </td>
                            <td class="py-3 px-2">
                                @if ($exam->show_results_immediately)
                                    <x-badge variant="{{ $attempt->passed ? 'success' : 'warning' }}">{{ $attempt->passed ? 'ناجح' : 'راسب' }}</x-badge>
                                @else
                                    <x-badge>{{ $attempt->status->label() }}</x-badge>
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                <a href="{{ route('student.exams.result', [$exam, $attempt]) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">عرض</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-dashboard-layout>
@endsection
