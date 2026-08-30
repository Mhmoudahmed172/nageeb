@extends('layouts.app')

@section('title', 'الاختبارات — نجيب')

@section('content')
<x-dashboard-layout title="الاختبارات" role-label="المعلّم" active-menu="quizzes">
    @include('teacher.exams._alerts')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="nageeb-heading-2">الاختبارات</h2>
            <p class="nageeb-text-muted text-sm mt-1">أنشئ اختبارات مرتبطة بموادك ووحداتك ودروسك.</p>
        </div>
        <x-button href="{{ route('teacher.exams.create') }}">+ إنشاء اختبار</x-button>
    </div>

    <div class="nageeb-kpi-strip nageeb-kpi-strip--5 mb-8">
        @foreach ([
            ['إجمالي الاختبارات', $stats['total']],
            ['المنشورة', $stats['published']],
            ['المسودات', $stats['drafts']],
            ['عدد المحاولات', $stats['attempts']],
            ['متوسط النتائج', $stats['average'] === null ? '—' : $stats['average'].'%'],
        ] as [$label, $value])
            <x-dashboard-stat :label="$label" :value="$value" />
        @endforeach
    </div>

    <form method="GET" action="{{ route('teacher.exams.index') }}" class="nageeb-card mb-6 grid gap-4 sm:grid-cols-4">
        <x-form-input label="بحث بالاسم" name="search" :value="$search" />
        <x-form-select label="المادة" name="course_id">
            <option value="">كل المواد</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) $courseId === (string) $course->id)>{{ $course->title }}</option>
            @endforeach
        </x-form-select>
        <x-form-select label="الحالة" name="status">
            <option value="">كل الحالات</option>
            @foreach (\App\Enums\ExamStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
            @endforeach
        </x-form-select>
        <div class="flex items-end">
            <button type="submit" class="nageeb-btn nageeb-btn--primary w-full">تصفية</button>
        </div>
    </form>

    <div class="nageeb-card nageeb-table-wrap">
        @if ($exams->isEmpty())
            <x-empty-state
                title="لا توجد اختبارات بعد."
                action-href="{{ route('teacher.exams.create') }}"
                action-label="+ إنشاء اختبار"
            >
                ابدأ بإنشاء اختبار وربطه بمادة أو وحدة أو درس.
            </x-empty-state>
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">اسم الاختبار</th>
                        <th class="py-3 px-2 font-medium">المادة</th>
                        <th class="py-3 px-2 font-medium">الوحدة</th>
                        <th class="py-3 px-2 font-medium">الدرس</th>
                        <th class="py-3 px-2 font-medium">النوع</th>
                        <th class="py-3 px-2 font-medium">الأسئلة</th>
                        <th class="py-3 px-2 font-medium">المحاولات</th>
                        <th class="py-3 px-2 font-medium">الحالة</th>
                        <th class="py-3 px-2 font-medium">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($exams as $exam)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2 font-semibold">{{ $exam->title }}</td>
                            <td class="py-3 px-2">{{ $exam->course->title }}</td>
                            <td class="py-3 px-2">{{ $exam->unit?->title ?? '—' }}</td>
                            <td class="py-3 px-2">{{ $exam->lesson?->title ?? '—' }}</td>
                            <td class="py-3 px-2">{{ $exam->delivery_mode->label() }}</td>
                            <td class="py-3 px-2">{{ $exam->isFileExam() ? '—' : $exam->questions_count }}</td>
                            <td class="py-3 px-2">{{ $exam->attempts_count }}</td>
                            <td class="py-3 px-2">
                                <x-badge variant="{{ $exam->status === \App\Enums\ExamStatus::Published ? 'success' : 'warning' }}">
                                    {{ $exam->status->label() }}
                                </x-badge>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('teacher.exams.show', $exam) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">عرض</a>
                                    <a href="{{ route('teacher.exams.edit', $exam) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">تعديل</a>
                                    @unless ($exam->isFileExam())
                                        <a href="{{ route('teacher.exams.questions.index', $exam) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">إدارة الأسئلة</a>
                                    @endunless
                                    <a href="{{ route('teacher.exams.results.index', $exam) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">النتائج</a>
                                    <form method="POST" action="{{ route('teacher.exams.destroy', $exam) }}" onsubmit="return confirm('حذف هذا الاختبار؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm text-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
