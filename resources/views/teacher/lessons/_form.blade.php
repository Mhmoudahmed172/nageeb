@props(['course', 'selectedUnitId' => null])

@php
    use App\Enums\ContentStatus;
@endphp

<form method="POST" action="{{ route('teacher.courses.lessons.store', $course) }}" class="lesson-create">
    @csrf

    <div class="lesson-create__intro">
        <h2 class="nageeb-heading-2">درس جديد</h2>
        <p class="nageeb-text-muted text-sm mt-1">
            أنشئ الدرس أولًا، ثم أضف الفيديوهات والنصوص والملفات من محرر المحتوى.
        </p>
    </div>

    <div class="lesson-create__fields">
        <x-form-input label="عنوان الدرس" name="title" required maxlength="255" />

        <x-form-textarea label="الوصف" name="description" rows="5" />

        <x-form-select label="الوحدة" name="unit_id" required help="تظهر وحدات هذه المادة فقط.">
            @foreach ($course->semesters as $semester)
                @foreach ($semester->units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('unit_id', $selectedUnitId) === (string) $unit->id)>
                        {{ $semester->title }} — {{ $unit->title }}
                    </option>
                @endforeach
            @endforeach
        </x-form-select>

        <div class="lesson-create__row">
            <fieldset class="nageeb-field">
                <legend class="nageeb-label">الحالة <span class="text-alert">*</span></legend>
                <div class="nageeb-segmented" role="radiogroup" aria-label="حالة الدرس">
                    @foreach ([ContentStatus::Draft->value => 'مسودة', ContentStatus::Live->value => 'منشور'] as $value => $label)
                        <label class="nageeb-segmented__option">
                            <input type="radio" name="status" value="{{ $value }}" @checked(old('status', ContentStatus::Draft->value) === $value) required>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('status')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
            </fieldset>

            <x-form-input label="المدة التقديرية (دقيقة)" name="estimated_duration" type="number" min="1" max="1440" />
        </div>

        <div class="nageeb-field">
            <label class="nageeb-toggle">
                <input type="hidden" name="is_preview" value="0">
                <input type="checkbox" name="is_preview" value="1" class="nageeb-checkbox" @checked(old('is_preview'))>
                <span>معاينة مجانية</span>
            </label>
            <p class="nageeb-field-help">يتيح عرض الدرس قبل الاشتراك.</p>
        </div>
    </div>

    <div class="lesson-create__actions">
        <a href="{{ route('teacher.courses.content', $course) }}" class="nageeb-btn nageeb-btn--ghost">إلغاء</a>
        <x-button type="submit" name="save_action" value="draft" variant="outline">حفظ كمسودة</x-button>
        <x-button type="submit" name="save_action" value="save">إنشاء ومتابعة إلى المحتوى</x-button>
    </div>
</form>
