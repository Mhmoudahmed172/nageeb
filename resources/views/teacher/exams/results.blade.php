@extends('layouts.app')

@section('title', 'نتائج الاختبار — نجيب')

@section('content')
<x-dashboard-layout title="نتائج: {{ $exam->title }}" role-label="المعلّم" active-menu="quizzes">
    @include('teacher.exams._alerts')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="nageeb-caption">{{ $exam->course->title }}</p>
            <h2 class="nageeb-heading-2 mt-1">نتائج الاختبار</h2>
        </div>
        <x-button href="{{ route('teacher.exams.show', $exam) }}" variant="outline" size="sm">عرض الاختبار</x-button>
    </div>

    <div class="grid gap-4 sm:grid-cols-4 mb-6">
        @foreach ([
            ['عدد المحاولات', $stats['attempts']],
            ['عدد الطلاب', $stats['students']],
            ['متوسط النتائج', $stats['average'] === null ? '—' : $stats['average'].'%'],
            ['عدد الناجحين', $stats['passed']],
        ] as [$label, $value])
            <div class="nageeb-card">
                <p class="nageeb-caption">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="nageeb-card nageeb-table-wrap">
        @if ($attempts->isEmpty())
            <x-empty-state title="لا توجد محاولات على هذا الاختبار بعد." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">الطالب</th>
                        <th class="py-3 px-2 font-medium">المحاولة</th>
                        <th class="py-3 px-2 font-medium">البداية</th>
                        <th class="py-3 px-2 font-medium">التسليم</th>
                        <th class="py-3 px-2 font-medium">الدرجة</th>
                        <th class="py-3 px-2 font-medium">النسبة</th>
                        <th class="py-3 px-2 font-medium">الحالة</th>
                        <th class="py-3 px-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attempts as $attempt)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2">{{ $attempt->student->name }}</td>
                            <td class="py-3 px-2">{{ $attempt->attempt_number }}</td>
                            <td class="py-3 px-2">{{ $attempt->started_at?->format('Y/m/d H:i') ?? '—' }}</td>
                            <td class="py-3 px-2">{{ $attempt->submitted_at?->format('Y/m/d H:i') ?? '—' }}</td>
                            <td class="py-3 px-2">{{ (float) $attempt->score }} / {{ (float) $attempt->total_points }}</td>
                            <td class="py-3 px-2">{{ (float) $attempt->percentage }}%</td>
                            <td class="py-3 px-2">
                                @if ($attempt->status === \App\Enums\AttemptStatus::InProgress)
                                    <x-badge variant="info">{{ $attempt->status->label() }}</x-badge>
                                @else
                                    <x-badge variant="{{ $attempt->passed ? 'success' : 'warning' }}">
                                        {{ $attempt->passed ? 'ناجح' : 'راسب' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                <a href="{{ route('teacher.exams.results.show', [$exam, $attempt]) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">التفاصيل</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
