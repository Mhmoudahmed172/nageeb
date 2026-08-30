@extends('layouts.app')

@section('title', $exam->title.' — نجيب')

@section('content')
@php
    use App\Enums\QuestionType;

    $remaining = $attempt->secondsRemaining();
    $answeredIds = $answers->filter(fn ($answer) => ! empty($answer->selectedIds()))->keys();
@endphp

<x-dashboard-layout title="{{ $exam->title }}" role-label="الطالب" active-menu="exams">
    <div
        class="nageeb-exam-focus grid gap-6 lg:grid-cols-[minmax(0,1fr)_16rem] items-start"
        @if ($remaining !== null)
            x-data="{
                left: {{ $remaining }},
                init() {
                    const tick = setInterval(() => {
                        this.left = this.left - 1;
                        if (this.left <= 0) { clearInterval(tick); this.$refs.autoSubmit.click(); }
                    }, 1000);
                },
                get clock() {
                    const minutes = Math.floor(Math.max(0, this.left) / 60);
                    const seconds = Math.max(0, this.left) % 60;
                    return minutes + ':' + String(seconds).padStart(2, '0');
                },
            }"
        @else
            x-data="{}"
        @endif
    >
        <form method="POST" action="{{ route('student.exams.answer', [$exam, $attempt]) }}" class="nageeb-card">
            @csrf
            <input type="hidden" name="question_id" value="{{ $question->id }}">

            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <p class="nageeb-caption">السؤال {{ $index + 1 }} من {{ $questions->count() }}</p>
                <div class="flex items-center gap-2">
                    <x-badge>{{ $question->type->label() }}</x-badge>
                    <span class="nageeb-caption">{{ $exam->pointsFor($question) }} درجة</span>
                </div>
            </div>
            <x-progress :value="$questions->count() ? round((($index + 1) / $questions->count()) * 100) : 0" label="تقدم الاختبار" class="mb-5" />

            <p class="text-lg font-semibold mb-4">{{ $question->text }}</p>

            @if ($question->type === QuestionType::MultipleResponse)
                <p class="nageeb-caption mb-2">يمكن اختيار أكثر من إجابة.</p>
            @endif

            <div class="grid gap-2">
                @foreach ($options as $option)
                    <label class="nageeb-choice-row">
                        <input
                            type="{{ $question->type === QuestionType::MultipleResponse ? 'checkbox' : 'radio' }}"
                            name="option_ids[]"
                            value="{{ $option->id }}"
                            class="nageeb-checkbox"
                            @checked(in_array($option->id, $selected, true))
                        >
                        <span class="text-sm">{{ $option->text }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 mt-6">
                <div class="flex gap-2">
                    <button
                        type="submit"
                        name="goto"
                        value="{{ max(0, $index - 1) }}"
                        class="nageeb-btn nageeb-btn--outline"
                        @disabled($index === 0)
                    >السابق</button>

                    @if ($index < $questions->count() - 1)
                        <button type="submit" name="goto" value="{{ $index + 1 }}" class="nageeb-btn nageeb-btn--primary">التالي</button>
                    @endif
                </div>

                <button
                    type="submit"
                    formaction="{{ route('student.exams.submit', [$exam, $attempt]) }}"
                    class="nageeb-btn nageeb-btn--primary"
                    onclick="return confirm('تسليم الاختبار الآن؟')"
                >تسليم الاختبار</button>
            </div>

            @if ($remaining !== null)
                <button
                    type="submit"
                    formaction="{{ route('student.exams.submit', [$exam, $attempt]) }}"
                    x-ref="autoSubmit"
                    class="hidden"
                    aria-hidden="true"
                >تسليم تلقائي</button>
            @endif
        </form>

        <aside class="nageeb-card grid gap-4">
            @if ($remaining !== null)
                <div>
                    <p class="nageeb-caption">الوقت المتبقي</p>
                    <p class="text-2xl font-bold font-mono" x-text="clock"></p>
                </div>
            @else
                <p class="nageeb-caption">اختبار بدون وقت محدد</p>
            @endif

            <div>
                <p class="nageeb-caption mb-2">الأسئلة</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($questions as $navIndex => $navQuestion)
                        <form method="POST" action="{{ route('student.exams.answer', [$exam, $attempt]) }}">
                            @csrf
                            <input type="hidden" name="question_id" value="{{ $question->id }}">
                            @foreach ($selected as $selectedId)
                                <input type="hidden" name="option_ids[]" value="{{ $selectedId }}">
                            @endforeach
                            <button
                                type="submit"
                                name="goto"
                                value="{{ $navIndex }}"
                                @class([
                                    'size-9 rounded-md border text-sm font-mono',
                                    'bg-primary text-white border-primary' => $navIndex === $index,
                                    'bg-success-muted border-success text-success' => $navIndex !== $index && $answeredIds->contains($navQuestion->id),
                                    'border-border' => $navIndex !== $index && ! $answeredIds->contains($navQuestion->id),
                                ])
                            >{{ $navIndex + 1 }}</button>
                        </form>
                    @endforeach
                </div>
            </div>

            <p class="nageeb-caption">
                تم حفظ {{ $answeredIds->count() }} من {{ $questions->count() }} إجابة. يمكنك إغلاق الصفحة والعودة لاحقًا.
            </p>
        </aside>
    </div>
</x-dashboard-layout>
@endsection
