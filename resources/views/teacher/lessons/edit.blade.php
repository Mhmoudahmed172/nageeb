@extends('layouts.app')

@section('title', $lesson->title.' — محرر الدرس')

@section('content')
@php
    use App\Enums\ContentStatus;
    use App\Enums\LessonContentType;

    $unit = $lesson->unit;
    $semester = $unit->semester;
    $typeIcons = [
        'video' => 'video',
        'text' => 'text',
        'file' => 'file',
        'audio' => 'audio',
        'link' => 'link',
        'quiz' => 'quiz',
        'assignment' => 'assignment',
        'live_session' => 'live',
    ];
@endphp

<x-course-workspace :course="$course" active="content">
    <div
        class="lesson-builder"
        x-data="lessonBuilder"
        data-csrf="{{ csrf_token() }}"
    >
        <header class="lesson-builder__bar">
            <div class="min-w-0">
                <nav class="lesson-builder__path" aria-label="مسار الدرس">
                    <a href="{{ route('teacher.courses.content', $course) }}">{{ $course->title }}</a>
                    <span aria-hidden="true">›</span>
                    <span>{{ $semester->title }}</span>
                    <span aria-hidden="true">›</span>
                    <span>{{ $unit->title }}</span>
                </nav>
                <h2 class="nageeb-heading-2 truncate" x-text="title">{{ $lesson->title }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <x-badge variant="{{ $lesson->status === ContentStatus::Live ? 'success' : 'warning' }}">{{ $lesson->status->label() }}</x-badge>
                    @if ($lesson->is_preview)<x-badge variant="info">معاينة مجانية</x-badge>@endif
                    <span class="nageeb-caption">{{ $lesson->contents->count() }} كتلة محتوى</span>
                </div>
            </div>

            <div class="lesson-builder__actions">
                <span class="lesson-builder__save" :data-state="state" x-text="stateLabel()"></span>
                <x-button href="{{ route('teacher.courses.preview', $course) }}" variant="outline" size="sm">معاينة</x-button>
                <x-button type="submit" form="lesson-settings" name="save_action" value="save" variant="secondary" size="sm">حفظ</x-button>
                <x-button type="submit" form="lesson-settings" name="save_action" value="publish" size="sm">نشر</x-button>
            </div>
        </header>

        <div class="lesson-builder__grid">
            <section class="lesson-builder__canvas">
                <div class="lesson-builder__canvas-head">
                    <div>
                        <h3 class="nageeb-heading-3">محتوى الدرس</h3>
                        <p class="nageeb-caption mt-1">اسحب الكتل لإعادة ترتيبها. تُحفظ تعديلات كل كتلة تلقائيًا.</p>
                    </div>

                    <div class="lesson-builder__add" x-data="{ open: false }" @click.outside="open = false">
                        <x-button size="sm" x-on:click="open = ! open" ::aria-expanded="open">+ إضافة محتوى</x-button>
                        <div class="lesson-builder__menu" x-show="open" x-cloak x-transition>
                            @foreach (LessonContentType::cases() as $type)
                                <button type="button" @click="open = false; pickType('{{ $type->value }}')">
                                    <x-nav-icon :name="$typeIcons[$type->value]" class="size-4" />
                                    <span>{{ $type->label() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form
                    id="add-content"
                    method="POST"
                    action="{{ route('teacher.courses.lesson-contents.store', [$course, $lesson]) }}"
                    x-ref="addForm"
                    class="hidden"
                >
                    @csrf
                    <input type="hidden" name="type" x-ref="type">
                </form>
                <input type="file" class="hidden" x-ref="file" @change="uploadSelected()">

                @if ($lesson->contents->isEmpty())
                    <div class="nageeb-empty">
                        <p class="nageeb-empty__title">ابدأ ببناء محتوى هذا الدرس.</p>
                        <div class="nageeb-empty__body">أضف فيديو أو نصًا أو ملفًا، ويمكنك دمج أكثر من نوع داخل الدرس الواحد.</div>
                    </div>
                @else
                    <div
                        class="lesson-blocks"
                        data-sortable
                        data-reorder-url="{{ route('teacher.courses.lesson-contents.reorder', [$course, $lesson]) }}"
                    >
                        @foreach ($lesson->contents as $index => $content)
                            @include('teacher.lessons._block', [
                                'course' => $course,
                                'lesson' => $lesson,
                                'content' => $content,
                                'regions' => $regions,
                                'index' => $index,
                            ])
                        @endforeach
                    </div>
                @endif

                <div class="lesson-block lesson-block--uploading" x-show="upload" x-cloak>
                    <div class="lesson-block__head">
                        <span class="lesson-block__index">••</span>
                        <span class="lesson-block__type"><span class="nageeb-spinner"></span></span>
                        <span class="lesson-block__title"><span class="truncate" x-text="upload?.name"></span></span>
                        <span class="nageeb-caption" x-text="uploadLabel()"></span>
                    </div>
                    <div class="lesson-block__progress" role="progressbar" :aria-valuenow="upload?.percent ?? 0" aria-valuemin="0" aria-valuemax="100">
                        <span :style="`width: ${upload?.percent ?? 0}%`"></span>
                    </div>
                </div>
            </section>

            <aside class="lesson-builder__settings">
                <h3 class="nageeb-heading-3 mb-4">إعدادات الدرس</h3>

                <form
                    id="lesson-settings"
                    method="POST"
                    action="{{ route('teacher.courses.lessons.update', [$course, $lesson]) }}"
                    @input="markDirty()"
                    @change="markDirty()"
                    @submit="submitting = true"
                    class="grid gap-4"
                >
                    @csrf
                    @method('PUT')

                    <x-form-input
                        label="عنوان الدرس"
                        name="title"
                        required
                        :value="$lesson->title"
                        maxlength="255"
                        x-model="title"
                    />

                    <x-form-textarea
                        label="الوصف"
                        name="description"
                        :value="$lesson->description"
                        rows="5"
                    />

                    <x-form-select label="الوحدة" name="unit_id" required>
                        @foreach ($course->semesters as $courseSemester)
                            @foreach ($courseSemester->units as $courseUnit)
                                <option value="{{ $courseUnit->id }}" @selected((string) old('unit_id', $selectedUnitId) === (string) $courseUnit->id)>
                                    {{ $courseSemester->title }} — {{ $courseUnit->title }}
                                </option>
                            @endforeach
                        @endforeach
                    </x-form-select>

                    <fieldset class="nageeb-field">
                        <legend class="nageeb-label">الحالة <span class="text-alert">*</span></legend>
                        <div class="nageeb-segmented" role="radiogroup" aria-label="حالة الدرس">
                            @foreach ([ContentStatus::Draft->value => 'مسودة', ContentStatus::Live->value => 'منشور'] as $value => $label)
                                <label class="nageeb-segmented__option">
                                    <input type="radio" name="status" value="{{ $value }}" @checked(old('status', $lesson->status->value) === $value) required>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('status')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="nageeb-field">
                        <label class="nageeb-toggle">
                            <input type="hidden" name="is_preview" value="0">
                            <input type="checkbox" name="is_preview" value="1" class="nageeb-checkbox" @checked(old('is_preview', $lesson->is_preview))>
                            <span>معاينة مجانية</span>
                        </label>
                        <p class="nageeb-field-help">يتيح عرض الدرس قبل الاشتراك.</p>
                    </div>

                    <x-form-input
                        label="المدة التقديرية (دقيقة)"
                        name="estimated_duration"
                        type="number"
                        min="1"
                        max="1440"
                        :value="$lesson->estimated_duration"
                    />
                </form>

                <div class="lesson-builder__settings-actions">
                    <x-button type="submit" form="lesson-settings" name="save_action" value="save" class="w-full">حفظ التعديلات</x-button>
                    <a href="{{ route('teacher.courses.content', $course) }}" class="nageeb-btn nageeb-btn--ghost w-full">العودة إلى المحتوى</a>
                </div>
            </aside>
        </div>
    </div>
</x-course-workspace>
@endsection
