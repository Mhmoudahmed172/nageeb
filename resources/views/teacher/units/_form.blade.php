@props(['course', 'unit' => null, 'suggestedTitle' => ''])

@php
    $isEdit = $unit !== null;
    $action = $isEdit
        ? route('teacher.courses.units.update', [$course, $unit])
        : route('teacher.courses.units.store', $course);
@endphp

<form method="POST" action="{{ $action }}" class="nageeb-card max-w-xl grid gap-5">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <x-form-select label="الفصل الدراسي" name="semester_id" required>
        @foreach ($course->semesters as $semester)
            <option value="{{ $semester->id }}" @selected((string) old('semester_id', $unit?->semester_id ?? $course->defaultSemester()->id) === (string) $semester->id)>
                {{ $semester->title }}
            </option>
        @endforeach
    </x-form-select>

    <x-form-input
        label="اسم الوحدة"
        name="title"
        required
        :value="$unit?->title ?? $suggestedTitle"
        maxlength="255"
    />

    <x-form-textarea
        label="وصف اختياري"
        name="description"
        :value="$unit?->description"
        rows="4"
    />

    <fieldset class="nageeb-field">
        <legend class="nageeb-label">الحالة <span class="text-alert">*</span></legend>
        <div class="nageeb-segmented" role="radiogroup" aria-label="حالة الوحدة">
            @foreach (\App\Enums\ContentStatus::cases() as $status)
                @if ($status === \App\Enums\ContentStatus::Archived && ! $isEdit)
                    @continue
                @endif
                <label class="nageeb-segmented__option">
                    <input
                        type="radio"
                        name="status"
                        value="{{ $status->value }}"
                        @checked(old('status', $unit?->status->value ?? \App\Enums\ContentStatus::Draft->value) === $status->value)
                        required
                    >
                    <span>{{ $status === \App\Enums\ContentStatus::Live ? 'منشورة' : ($status === \App\Enums\ContentStatus::Draft ? 'مسودة' : $status->label()) }}</span>
                </label>
            @endforeach
        </div>
        @error('status')
            <p class="nageeb-field-error" role="alert">{{ $message }}</p>
        @enderror
    </fieldset>

    <p class="text-sm nageeb-text-dim">الترتيب يُدار تلقائياً حسب موقع الوحدة في قائمة المادة.</p>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="nageeb-btn nageeb-btn--primary">{{ $isEdit ? 'حفظ الوحدة' : 'إضافة الوحدة' }}</button>
        <a href="{{ route('teacher.courses.content', $course) }}" class="nageeb-btn nageeb-btn--outline">إلغاء</a>
    </div>
</form>
