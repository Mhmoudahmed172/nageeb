@extends('layouts.app')

@section('title', $course->title.' — المحتوى')

@section('content')
<x-course-workspace :course="$course" active="content">
    <div
        x-data="curriculumWorkspace"
        data-csrf="{{ csrf_token() }}"
        class="curriculum-workspace"
    >
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-7">
            <div>
                <h2 class="nageeb-heading-2">محتوى المادة</h2>
                <p class="nageeb-text-muted text-sm mt-1">رتّب الفصول والوحدات والدروس بالسحب من مقبض الترتيب.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-button href="{{ route('teacher.courses.semesters.create', $course) }}" variant="outline">+ إضافة فصل</x-button>
                <x-button href="{{ route('teacher.courses.units.create', $course) }}">+ إضافة وحدة</x-button>
            </div>
        </div>

        @forelse ($course->semesters as $semesterIndex => $semester)
            @if ($loop->first)
                <div
                    class="curriculum-semesters space-y-8"
                    data-sortable
                    data-reorder-url="{{ route('teacher.courses.semesters.reorder', $course) }}"
                >
            @endif

            <section class="curriculum-semester" data-id="{{ $semester->id }}">
                <header class="flex items-center gap-3 mb-4">
                    <button type="button" class="drag-handle" aria-label="اسحب لترتيب الفصل" title="اسحب للترتيب">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><circle cx="7" cy="5" r="1"/><circle cx="13" cy="5" r="1"/><circle cx="7" cy="10" r="1"/><circle cx="13" cy="10" r="1"/><circle cx="7" cy="15" r="1"/><circle cx="13" cy="15" r="1"/></svg>
                    </button>
                    <span class="grid size-8 place-items-center rounded-md bg-primary text-white font-mono text-sm">{{ sprintf('%02d', $semesterIndex + 1) }}</span>
                    <div class="min-w-0 flex-1">
                        <h3 class="nageeb-heading-2 truncate">{{ $semester->title }}</h3>
                        <p class="nageeb-caption mt-1">{{ $semester->units->count() }} وحدة · {{ $semester->units->sum(fn ($unit) => $unit->lessons->count()) }} درس</p>
                    </div>
                    <x-button href="{{ route('teacher.courses.semesters.edit', [$course, $semester]) }}" variant="ghost" size="sm">تعديل</x-button>
                </header>

                <div class="sm:ms-11">
                    @if ($semester->units->isEmpty())
                        <div class="curriculum-inline-empty">
                            <p class="font-semibold">أضف أول وحدة لهذا الفصل.</p>
                            <x-button href="{{ route('teacher.courses.units.create', ['course' => $course, 'semester' => $semester->id]) }}" variant="outline" size="sm">+ إضافة وحدة</x-button>
                        </div>
                    @else
                        <div
                            class="curriculum-units space-y-2"
                            data-sortable
                            data-reorder-url="{{ route('teacher.courses.units.reorder', [$course, $semester]) }}"
                        >
                            @foreach ($semester->units as $unit)
                                <details class="curriculum-unit group" data-id="{{ $unit->id }}" open>
                                    <summary class="curriculum-unit__summary">
                                        <span class="drag-handle" aria-label="اسحب لترتيب الوحدة">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><circle cx="7" cy="5" r="1"/><circle cx="13" cy="5" r="1"/><circle cx="7" cy="10" r="1"/><circle cx="13" cy="10" r="1"/><circle cx="7" cy="15" r="1"/><circle cx="13" cy="15" r="1"/></svg>
                                        </span>
                                        <svg class="size-4 transition-transform group-open:rotate-180 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="m5.5 7.5 4.5 4 4.5-4"/></svg>
                                        <span class="font-bold min-w-0 truncate">{{ $unit->title }}</span>
                                        <span class="nageeb-caption ms-auto">{{ $unit->lessonsCountLabel() }}</span>
                                        <a href="{{ route('teacher.courses.units.edit', [$course, $unit]) }}" class="text-xs font-semibold relative z-10" onclick="event.stopPropagation()">تعديل</a>
                                    </summary>

                                    <div class="curriculum-unit__body">
                                        @if ($unit->lessons->isEmpty())
                                            <div class="curriculum-inline-empty">
                                                <p class="text-sm">أضف أول درس لهذه الوحدة.</p>
                                                <x-button href="{{ route('teacher.courses.lessons.create', ['course' => $course, 'unit' => $unit->id]) }}" variant="outline" size="sm">+ إضافة درس</x-button>
                                            </div>
                                        @else
                                            <ol
                                                class="curriculum-lessons"
                                                data-sortable
                                                data-reorder-url="{{ route('teacher.courses.lessons.reorder', [$course, $unit]) }}"
                                            >
                                                @foreach ($unit->lessons as $lesson)
                                                    @php($videoCount = $lesson->contents->where('type', \App\Enums\LessonContentType::Video)->count())
                                                    <li class="curriculum-lesson" data-id="{{ $lesson->id }}">
                                                        <span class="drag-handle" aria-label="اسحب لترتيب الدرس">
                                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><circle cx="7" cy="5" r="1"/><circle cx="13" cy="5" r="1"/><circle cx="7" cy="10" r="1"/><circle cx="13" cy="10" r="1"/><circle cx="7" cy="15" r="1"/><circle cx="13" cy="15" r="1"/></svg>
                                                        </span>
                                                        <span class="curriculum-lesson__number">{{ sprintf('%02d', $lesson->position) }}</span>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <h4 class="font-semibold text-sm truncate">{{ $lesson->title }}</h4>
                                                                @if ($lesson->is_preview)<x-badge variant="info">معاينة</x-badge>@endif
                                                            </div>
                                                            <p class="nageeb-caption mt-1">
                                                                {{ $lesson->contents->count() }} محتوى
                                                                @if ($videoCount) · {{ $videoCount }} فيديو @endif
                                                                @if ($lesson->estimated_duration) · {{ $lesson->estimated_duration }} دقيقة @endif
                                                            </p>
                                                        </div>
                                                        <x-badge variant="{{ $lesson->status->value === 'live' ? 'success' : 'warning' }}">{{ $lesson->status->label() }}</x-badge>
                                                        <div class="curriculum-lesson__actions" x-data="{ open: false }">
                                                            <button type="button" class="nageeb-btn nageeb-btn--ghost nageeb-btn--icon nageeb-btn--sm" @click="open = !open" aria-label="إجراءات الدرس">•••</button>
                                                            <div x-show="open" x-cloak x-transition @click.outside="open = false" class="curriculum-actions-menu">
                                                                <a href="{{ route('teacher.courses.lessons.edit', [$course, $lesson]) }}">تعديل</a>
                                                                <form method="POST" action="{{ route('teacher.courses.lessons.duplicate', [$course, $lesson]) }}">@csrf<button type="submit">نسخ</button></form>
                                                                @if ($course->units->count() > 1)
                                                                    <form method="POST" action="{{ route('teacher.courses.lessons.relocate', [$course, $lesson]) }}">
                                                                        @csrf
                                                                        <label class="nageeb-caption block mb-1" for="move-{{ $lesson->id }}">نقل إلى</label>
                                                                        <select id="move-{{ $lesson->id }}" name="unit_id" class="nageeb-select text-xs" onchange="this.form.submit()">
                                                                            @foreach ($course->semesters as $targetSemester)
                                                                                @foreach ($targetSemester->units as $targetUnit)
                                                                                    <option value="{{ $targetUnit->id }}" @selected($targetUnit->id === $unit->id)>{{ $targetSemester->title }} — {{ $targetUnit->title }}</option>
                                                                                @endforeach
                                                                            @endforeach
                                                                        </select>
                                                                    </form>
                                                                @endif
                                                                <form method="POST" action="{{ route('teacher.courses.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('حذف هذا الدرس؟')">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="text-danger">حذف</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        @endif
                                        <a href="{{ route('teacher.courses.lessons.create', ['course' => $course, 'unit' => $unit->id]) }}" class="inline-flex mt-3 text-sm font-semibold">+ إضافة درس</a>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                        <a href="{{ route('teacher.courses.units.create', ['course' => $course, 'semester' => $semester->id]) }}" class="inline-flex mt-3 text-sm font-semibold">+ إضافة وحدة</a>
                    @endif
                </div>
            </section>

            @if ($loop->last)</div>@endif
        @empty
            <x-empty-state
                title="ابدأ بتنظيم المادة إلى فصول."
                action-href="{{ route('teacher.courses.semesters.create', $course) }}"
                action-label="+ إضافة فصل"
            />
        @endforelse

        <div x-show="saving" x-cloak class="curriculum-save-state"><span class="nageeb-spinner"></span> جارٍ حفظ الترتيب…</div>
    </div>
</x-course-workspace>
@endsection
