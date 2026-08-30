@extends('layouts.app')

@section('title', 'نتيجة الاختبار — نجيب')

@section('content')
<x-dashboard-layout title="نتيجة: {{ $exam->title }}" role-label="الطالب" active-menu="exams">
    <div class="nageeb-card mb-6">
        <p class="nageeb-caption">{{ $exam->course->title }}</p>
        <h2 class="nageeb-heading-2 mt-1">{{ $exam->title }}</h2>

        @if ($attempt->status === \App\Enums\AttemptStatus::Expired)
            <div class="nageeb-alert nageeb-alert--warning mt-4">انتهى وقت الاختبار وتم تسليم إجاباتك تلقائيًا.</div>
        @endif

        @if (! $exam->show_results_immediately)
            <div class="nageeb-alert nageeb-alert--info mt-4">
                تم تسليم إجاباتك بنجاح. سيعلن المعلّم النتيجة لاحقًا.
            </div>
        @else
            <div class="nageeb-kpi-strip mt-6">
                @foreach ([
                    ['الدرجة', ((float) $attempt->score).' / '.((float) $attempt->total_points)],
                    ['النسبة', ((float) $attempt->percentage).'%'],
                    ['درجة النجاح', ((float) $exam->passing_score).'%'],
                    ['المحاولة', $attempt->attempt_number.' / '.$exam->max_attempts],
                ] as [$label, $value])
                    <x-dashboard-stat :label="$label" :value="$value" />
                @endforeach
            </div>

            <div class="mt-4">
                <x-badge variant="{{ $attempt->passed ? 'success' : 'warning' }}">
                    {{ $attempt->passed ? 'ناجح' : 'لم تجتز الاختبار' }}
                </x-badge>
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-2">
            <x-button href="{{ route('student.exams.show', $exam) }}" variant="outline" size="sm">صفحة الاختبار</x-button>
            <x-button href="{{ route('student.exams.index') }}" variant="ghost" size="sm">كل الاختبارات</x-button>
        </div>
    </div>

    @if ($exam->show_results_immediately && $exam->show_correct_answers)
        <div class="nageeb-card grid gap-4">
            <h3 class="nageeb-title-section">مراجعة الإجابات</h3>

            @foreach ($questions as $question)
                @php($answer = $attempt->answers->firstWhere('question_id', $question->id))
                @php($selected = $answer?->selectedIds() ?? [])
                <div class="border border-border rounded-md p-3">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="font-mono text-xs nageeb-text-dim">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <x-badge variant="{{ $answer?->is_correct ? 'success' : 'warning' }}">
                            {{ $answer?->is_correct ? 'صحيحة' : 'خاطئة' }}
                        </x-badge>
                        <span class="nageeb-caption ms-auto">
                            {{ (float) ($answer->points_awarded ?? 0) }} / {{ $exam->pointsFor($question) }} درجة
                        </span>
                    </div>
                    <p class="text-sm font-medium">{{ $question->text }}</p>
                    <ul class="grid gap-1 mt-2">
                        @foreach ($question->options as $option)
                            @php($isSelected = in_array($option->id, $selected, true))
                            <li @class([
                                'text-sm',
                                'text-success font-semibold' => $option->is_correct,
                                'text-danger' => $isSelected && ! $option->is_correct,
                                'nageeb-text-muted' => ! $option->is_correct && ! $isSelected,
                            ])>
                                {{ $isSelected ? '◉' : '○' }} {{ $option->text }}
                                @if ($option->is_correct) <span class="nageeb-caption">(الإجابة الصحيحة)</span> @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($question->explanation)
                        <p class="nageeb-caption mt-2">{{ $question->explanation }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-dashboard-layout>
@endsection
