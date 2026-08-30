@extends('layouts.app')

@section('title', 'بنك الأسئلة — نجيب')

@section('content')
<x-dashboard-layout title="بنك الأسئلة" role-label="المعلّم" active-menu="questions">
    @include('teacher.exams._alerts')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="nageeb-heading-2">بنك الأسئلة</h2>
            <p class="nageeb-text-muted text-sm mt-1">{{ $total }} سؤال في بنكك الخاص. يمكن إعادة استخدام أي سؤال في أكثر من اختبار.</p>
        </div>
        <x-button href="{{ route('teacher.questions.create') }}">+ إضافة سؤال</x-button>
    </div>

    <form method="GET" action="{{ route('teacher.questions.index') }}" class="nageeb-card mb-6 grid gap-4 sm:grid-cols-3 xl:grid-cols-6">
        <x-form-input label="بحث في نص السؤال" name="search" :value="$search" />
        <x-form-select label="المادة" name="course_id">
            <option value="">كل المواد</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) $courseId === (string) $course->id)>{{ $course->title }}</option>
            @endforeach
        </x-form-select>
        <x-form-select label="الوحدة" name="unit_id">
            <option value="">كل الوحدات</option>
            @foreach ($courses as $course)
                @foreach ($course->semesters as $semester)
                    @foreach ($semester->units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) $unitId === (string) $unit->id)>
                            {{ $course->title }} — {{ $unit->title }}
                        </option>
                    @endforeach
                @endforeach
            @endforeach
        </x-form-select>
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
        <div class="flex items-end">
            <button type="submit" class="nageeb-btn nageeb-btn--primary w-full">تصفية</button>
        </div>
    </form>

    <div class="nageeb-card nageeb-table-wrap">
        @if ($questions->isEmpty())
            <x-empty-state
                title="لا توجد أسئلة مطابقة."
                action-href="{{ route('teacher.questions.create') }}"
                action-label="+ إضافة سؤال"
            >
                ابدأ ببناء بنك أسئلتك لإعادة استخدامه في كل اختباراتك.
            </x-empty-state>
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">السؤال</th>
                        <th class="py-3 px-2 font-medium">النوع</th>
                        <th class="py-3 px-2 font-medium">المادة</th>
                        <th class="py-3 px-2 font-medium">الوحدة</th>
                        <th class="py-3 px-2 font-medium">الدرجة</th>
                        <th class="py-3 px-2 font-medium">الصعوبة</th>
                        <th class="py-3 px-2 font-medium">الاختبارات</th>
                        <th class="py-3 px-2 font-medium">آخر تعديل</th>
                        <th class="py-3 px-2 font-medium">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questions as $question)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2 max-w-sm">{{ \Illuminate\Support\Str::limit($question->text, 90) }}</td>
                            <td class="py-3 px-2">{{ $question->type->label() }}</td>
                            <td class="py-3 px-2">{{ $question->course?->title ?? '—' }}</td>
                            <td class="py-3 px-2">{{ $question->unit?->title ?? '—' }}</td>
                            <td class="py-3 px-2">{{ (float) $question->points }}</td>
                            <td class="py-3 px-2">{{ $question->difficulty->label() }}</td>
                            <td class="py-3 px-2">{{ $question->exams_count }}</td>
                            <td class="py-3 px-2">{{ $question->updated_at?->format('Y/m/d') }}</td>
                            <td class="py-3 px-2">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('teacher.questions.edit', $question) }}" class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm">تعديل</a>
                                    <form method="POST" action="{{ route('teacher.questions.destroy', $question) }}" onsubmit="return confirm('حذف هذا السؤال؟')">
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
