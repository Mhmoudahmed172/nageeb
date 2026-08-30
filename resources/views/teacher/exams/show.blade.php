@extends('layouts.app')

@section('title', $exam->title.' — نجيب')

@section('content')
<x-dashboard-layout title="{{ $exam->title }}" role-label="المعلّم" active-menu="quizzes">
    @include('teacher.exams._alerts')

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div class="min-w-0">
            <p class="nageeb-caption">{{ $exam->placementLabel() }}</p>
            <h2 class="nageeb-heading-2 mt-1">{{ $exam->title }}</h2>
            <div class="flex flex-wrap items-center gap-2 mt-2">
                <x-badge variant="{{ $exam->status === \App\Enums\ExamStatus::Published ? 'success' : 'warning' }}">{{ $exam->status->label() }}</x-badge>
                <x-badge>{{ $exam->delivery_mode->label() }}</x-badge>
                @unless ($exam->isFileExam())
                    <span class="nageeb-caption">{{ $exam->questions->count() }} سؤال · الدرجة الكاملة {{ $exam->totalPoints() }}</span>
                @else
                    <span class="nageeb-caption">{{ $exam->file_original_name ?? 'ملف الاختبار' }}</span>
                @endunless
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @unless ($exam->isFileExam())
                <x-button href="{{ route('teacher.exams.questions.index', $exam) }}" variant="outline" size="sm">إدارة الأسئلة</x-button>
            @endunless
            <x-button href="{{ route('teacher.exams.results.index', $exam) }}" variant="outline" size="sm">النتائج</x-button>
            <x-button href="{{ route('teacher.exams.edit', $exam) }}" size="sm">تعديل</x-button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 grid gap-6">
            @if ($exam->description)
                <div class="nageeb-card">
                    <h3 class="nageeb-title-section mb-2">الوصف</h3>
                    <p class="text-sm nageeb-text-muted">{{ $exam->description }}</p>
                </div>
            @endif

            @if ($exam->isFileExam())
                <div class="nageeb-card">
                    <h3 class="nageeb-title-section mb-4">ورقة الاختبار</h3>
                    @if ($exam->hasPaperFile())
                        <p class="text-sm mb-3">{{ $exam->file_original_name }}</p>
                        <x-button href="{{ $exam->paperUrl() }}" variant="outline" size="sm">فتح الملف</x-button>
                    @else
                        <p class="nageeb-text-muted text-sm">لم يُرفع ملف بعد.</p>
                    @endif
                </div>
            @else
            <div class="nageeb-card">
                <h3 class="nageeb-title-section mb-4">الأسئلة</h3>
                @if ($exam->questions->isEmpty())
                    <x-empty-state
                        title="لا توجد أسئلة في هذا الاختبار."
                        action-href="{{ route('teacher.exams.questions.index', $exam) }}"
                        action-label="إضافة أسئلة"
                    />
                @else
                    <ol class="grid gap-3">
                        @foreach ($exam->questions as $question)
                            <li class="border border-border rounded-md p-3">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="font-mono text-xs nageeb-text-dim">{{ sprintf('%02d', $loop->iteration) }}</span>
                                    <x-badge>{{ $question->type->label() }}</x-badge>
                                    <x-badge variant="info">{{ $question->difficulty->label() }}</x-badge>
                                    <span class="nageeb-caption ms-auto">{{ $exam->pointsFor($question) }} درجة</span>
                                </div>
                                <p class="text-sm font-medium">{{ $question->text }}</p>
                                <ul class="grid gap-1 mt-2">
                                    @foreach ($question->options as $option)
                                        <li class="text-sm {{ $option->is_correct ? 'text-success font-semibold' : 'nageeb-text-muted' }}">
                                            {{ $option->is_correct ? '✓' : '•' }} {{ $option->text }}
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
            @endif
        </div>

        <aside class="nageeb-card grid gap-3 h-fit">
            <h3 class="nageeb-title-section">الإعدادات</h3>
            @foreach ([
                'المدة' => $exam->duration_minutes ? $exam->duration_minutes.' دقيقة' : 'بلا وقت محدد',
                'عدد المحاولات' => $exam->max_attempts,
                'درجة النجاح' => ((float) $exam->passing_score).'%',
                'الدرجة الكاملة' => $exam->totalPoints(),
                'عدد المحاولات المسجلة' => $attemptsCount,
                'إظهار النتيجة مباشرة' => $exam->show_results_immediately ? 'نعم' : 'لا',
                'إظهار الإجابات الصحيحة' => $exam->show_correct_answers ? 'نعم' : 'لا',
                'ترتيب عشوائي للأسئلة' => $exam->shuffle_questions ? 'نعم' : 'لا',
                'ترتيب عشوائي للإجابات' => $exam->shuffle_options ? 'نعم' : 'لا',
                'المناطق المتاحة' => $exam->regions->isEmpty() ? 'جميع المناطق' : $exam->regions->pluck('name')->join('، '),
            ] as $label => $value)
                <div class="flex items-center justify-between gap-3 text-sm border-b border-border pb-2 last:border-0">
                    <span class="nageeb-text-muted">{{ $label }}</span>
                    <span class="font-medium">{{ $value }}</span>
                </div>
            @endforeach
        </aside>
    </div>
</x-dashboard-layout>
@endsection
