@extends('layouts.app')

@section('title', 'أسئلة الاختبار — نجيب')

@section('content')
<x-dashboard-layout title="أسئلة: {{ $exam->title }}" role-label="المعلّم" active-menu="quizzes">
    @include('teacher.exams._alerts')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="nageeb-caption">{{ $exam->placementLabel() }}</p>
            <h2 class="nageeb-heading-2 mt-1">إدارة أسئلة الاختبار</h2>
            <p class="nageeb-text-muted text-sm mt-1">{{ $exam->questions->count() }} سؤال · الدرجة الكاملة {{ $exam->totalPoints() }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-button href="{{ route('teacher.questions.create') }}" variant="outline" size="sm">+ سؤال جديد في البنك</x-button>
            <x-button href="{{ route('teacher.exams.show', $exam) }}" size="sm">عرض الاختبار</x-button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="nageeb-card">
            <h3 class="nageeb-title-section mb-4">أسئلة الاختبار</h3>

            @if ($exam->questions->isEmpty())
                <x-empty-state title="لم تُضف أسئلة بعد. اختر من بنك الأسئلة على اليسار." />
            @else
                <ol class="grid gap-3">
                    @foreach ($exam->questions as $question)
                        <li class="border border-border rounded-md p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs nageeb-text-dim">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <x-badge>{{ $question->type->label() }}</x-badge>
                                <div class="flex gap-1 ms-auto">
                                    <form method="POST" action="{{ route('teacher.exams.questions.update', [$exam, $question]) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm" aria-label="تحريك لأعلى">▲</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.exams.questions.update', [$exam, $question]) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm" aria-label="تحريك لأسفل">▼</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.exams.questions.destroy', [$exam, $question]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm text-danger">إزالة</button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-sm font-medium mt-2">{{ $question->text }}</p>
                            <form method="POST" action="{{ route('teacher.exams.questions.update', [$exam, $question]) }}" class="flex items-end gap-2 mt-3">
                                @csrf @method('PUT')
                                <div class="nageeb-field mb-0">
                                    <label class="nageeb-label" for="points-{{ $question->id }}">الدرجة داخل هذا الاختبار</label>
                                    <input
                                        id="points-{{ $question->id }}"
                                        type="number"
                                        name="points"
                                        step="0.25"
                                        min="0.25"
                                        max="100"
                                        class="nageeb-input"
                                        value="{{ $exam->pointsFor($question) }}"
                                    >
                                </div>
                                <button type="submit" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm">حفظ</button>
                            </form>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="nageeb-card">
            <h3 class="nageeb-title-section mb-4">بنك الأسئلة</h3>

            <form method="GET" action="{{ route('teacher.exams.questions.index', $exam) }}" class="grid gap-3 sm:grid-cols-3 mb-4">
                <x-form-input label="بحث" name="search" :value="$search" />
                <x-form-select label="النوع" name="type">
                    <option value="">كل الأنواع</option>
                    @foreach ($types as $case)
                        <option value="{{ $case->value }}" @selected($type === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </x-form-select>
                <x-form-select label="الصعوبة" name="difficulty">
                    <option value="">كل المستويات</option>
                    @foreach ($difficulties as $case)
                        <option value="{{ $case->value }}" @selected($difficulty === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </x-form-select>
                <div class="sm:col-span-3">
                    <button type="submit" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm">تصفية</button>
                </div>
            </form>

            @if ($bank->isEmpty())
                <x-empty-state
                    title="لا توجد أسئلة متاحة للإضافة."
                    action-href="{{ route('teacher.questions.create') }}"
                    action-label="+ إنشاء سؤال"
                />
            @else
                <ul class="grid gap-2">
                    @foreach ($bank as $question)
                        <li class="border border-border rounded-md p-3 flex flex-wrap items-center gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium">{{ $question->text }}</p>
                                <p class="nageeb-caption mt-1">
                                    {{ $question->type->label() }} · {{ $question->difficulty->label() }} · {{ (float) $question->points }} درجة · {{ $question->options_count }} خيار
                                </p>
                            </div>
                            <form method="POST" action="{{ route('teacher.exams.questions.store', $exam) }}">
                                @csrf
                                <input type="hidden" name="question_id" value="{{ $question->id }}">
                                <button type="submit" class="nageeb-btn nageeb-btn--primary nageeb-btn--sm">إضافة</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-dashboard-layout>
@endsection
