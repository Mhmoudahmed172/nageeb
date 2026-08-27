@props(['course', 'semester' => null])

@php
    $isEdit = $semester !== null;
    $action = $isEdit
        ? route('teacher.courses.semesters.update', [$course, $semester])
        : route('teacher.courses.semesters.store', $course);
@endphp

<form method="POST" action="{{ $action }}" class="nageeb-card max-w-xl grid gap-5">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <x-form-input label="اسم الفصل" name="title" required :value="$semester?->title ?? 'الفصل الأول'" maxlength="255" />

    <x-form-textarea label="وصف اختياري" name="description" :value="$semester?->description" rows="4" />

    <fieldset class="nageeb-field">
        <legend class="nageeb-label">الحالة <span class="text-alert">*</span></legend>
        <div class="nageeb-segmented" role="radiogroup">
            @foreach (\App\Enums\ContentStatus::cases() as $status)
                <label class="nageeb-segmented__option">
                    <input
                        type="radio"
                        name="status"
                        value="{{ $status->value }}"
                        @checked(old('status', $semester?->status->value ?? 'live') === $status->value)
                        required
                    >
                    <span>{{ $status->label() }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="nageeb-btn nageeb-btn--primary">{{ $isEdit ? 'حفظ الفصل' : 'إضافة الفصل' }}</button>
        <a href="{{ route('teacher.courses.content', $course) }}" class="nageeb-btn nageeb-btn--outline">إلغاء</a>
    </div>
</form>
