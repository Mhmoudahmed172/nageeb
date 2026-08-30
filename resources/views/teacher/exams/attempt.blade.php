@extends('layouts.app')

@section('title', 'تفاصيل المحاولة — نجيب')

@section('content')
<x-dashboard-layout title="محاولة {{ $attempt->student->name }}" role-label="المعلّم" active-menu="quizzes">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="nageeb-caption">{{ $exam->title }}</p>
            <h2 class="nageeb-heading-2 mt-1">{{ $attempt->student->name }}</h2>
            <p class="nageeb-text-muted text-sm mt-1">
                المحاولة {{ $attempt->attempt_number }} ·
                {{ (float) $attempt->score }} / {{ (float) $attempt->total_points }} ·
                {{ (float) $attempt->percentage }}%
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($attempt->status === \App\Enums\AttemptStatus::InProgress)
                <x-badge variant="info">{{ $attempt->status->label() }}</x-badge>
            @else
                <x-badge variant="{{ $attempt->passed ? 'success' : 'warning' }}">{{ $attempt->passed ? 'ناجح' : 'راسب' }}</x-badge>
            @endif
            <x-button href="{{ route('teacher.exams.results.index', $exam) }}" variant="outline" size="sm">كل النتائج</x-button>
        </div>
    </div>

    <div class="nageeb-card grid gap-4">
        @foreach ($exam->questions as $question)
            @php($answer = $attempt->answers->firstWhere('question_id', $question->id))
            @php($selected = $answer?->selectedIds() ?? [])
            <div class="border border-border rounded-md p-3">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="font-mono text-xs nageeb-text-dim">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <x-badge>{{ $question->type->label() }}</x-badge>
                    <span class="nageeb-caption ms-auto">
                        {{ (float) ($answer->points_awarded ?? 0) }} / {{ $exam->pointsFor($question) }} درجة
                    </span>
                    <x-badge variant="{{ $answer?->is_correct ? 'success' : 'warning' }}">
                        {{ $answer?->is_correct ? 'إجابة صحيحة' : 'إجابة خاطئة' }}
                    </x-badge>
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
            </div>
        @endforeach
    </div>
</x-dashboard-layout>
@endsection
