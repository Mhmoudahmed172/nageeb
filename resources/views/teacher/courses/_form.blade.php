@props(['course' => null])

@php
    $isEdit = $course !== null;
    $action = $isEdit ? route('teacher.courses.update', $course) : route('teacher.courses.store');
    $isFree = (bool) old('is_free', $course?->is_free ?? false);
    $coverUrl = $course?->coverUrl();
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    class="nageeb-course-form"
    x-data="{
        isFree: {{ $isFree ? 'true' : 'false' }},
        coverPreview: @js($coverUrl)
    }"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="nageeb-course-form__layout">
        <section class="nageeb-card nageeb-course-form__main grid gap-5">
            <h2 class="nageeb-title-section mb-5">تفاصيل المادة</h2>

            <x-form-input
                label="عنوان المادة (العربية)"
                name="title"
                required
                :value="$course?->title"
                maxlength="255"
            />

            <x-form-textarea
                label="الوصف (العربية)"
                name="description"
                :value="$course?->description"
                rows="8"
            />
        </section>

        <aside class="nageeb-card nageeb-course-form__aside">
            <h2 class="nageeb-title-section mb-5">إعدادات المادة</h2>

            <fieldset class="nageeb-field mb-5">
                <legend class="nageeb-label">
                    التوفر <span class="text-alert">*</span>
                </legend>
                <div class="nageeb-segmented" role="radiogroup" aria-label="التوفر">
                    @foreach (\App\Enums\CourseStatus::cases() as $status)
                        <label class="nageeb-segmented__option">
                            <input
                                type="radio"
                                name="status"
                                value="{{ $status->value }}"
                                @checked(old('status', $course?->status->value ?? 'draft') === $status->value)
                                required
                            >
                            <span>{{ $status->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('status')
                    <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                @enderror
            </fieldset>

            <x-form-select label="الصف الدراسي" name="grade_level" required>
                <option value="" disabled @selected(old('grade_level', $course?->grade_level?->value) === null)>اختر الصف</option>
                @foreach (\App\Enums\GradeLevel::cases() as $grade)
                    <option
                        value="{{ $grade->value }}"
                        @selected(old('grade_level', $course?->grade_level?->value) === $grade->value)
                    >{{ $grade->label() }}</option>
                @endforeach
            </x-form-select>

            <div class="nageeb-field mt-5">
                <label class="nageeb-toggle">
                    <input type="hidden" name="is_free" value="0">
                    <input
                        type="checkbox"
                        name="is_free"
                        value="1"
                        class="nageeb-checkbox"
                        x-model="isFree"
                        @checked($isFree)
                    >
                    <span>مادة مجانية</span>
                </label>
                <p class="text-sm nageeb-text-dim">عند التفعيل يصل الطلاب إلى المحتوى دون طلب اشتراك.</p>
                @error('is_free')
                    <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <x-form-input
                label="السعر المرجعي (₪)"
                name="reference_price"
                type="number"
                :value="$course?->reference_price"
                step="0.01"
                min="0"
                class="mt-5"
            />
            <p class="text-sm nageeb-text-dim mt-1" x-show="isFree">اختياري للعرض حتى لو كانت المادة مجانية.</p>

            <div class="nageeb-field mt-5">
                <label for="cover_image" class="nageeb-label">صورة الغلاف</label>
                <p class="text-sm nageeb-text-dim mb-2">النسبة المفضّلة 3:4 (مثلاً 900×1200).</p>
                <div class="nageeb-cover-preview" x-show="coverPreview">
                    <img :src="coverPreview" alt="معاينة الغلاف" class="nageeb-cover-preview__img">
                </div>
                <input
                    type="file"
                    id="cover_image"
                    name="cover_image"
                    accept="image/jpeg,image/png,image/webp"
                    class="nageeb-input"
                    @change="const file = $event.target.files[0]; coverPreview = file ? URL.createObjectURL(file) : coverPreview"
                >
                @error('cover_image')
                    <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="nageeb-field mt-5">
                <label for="attachments" class="nageeb-label">المرفقات</label>
                <p class="text-sm nageeb-text-dim mb-2">ملفات عامة للمادة (PDF أو صور). يمكن إضافة المزيد لاحقاً.</p>
                @if ($isEdit && $course->attachments->isNotEmpty())
                    <ul class="grid gap-1 mb-3 text-sm">
                        @foreach ($course->attachments as $attachment)
                            <li>
                                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener">{{ $attachment->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <input
                    type="file"
                    id="attachments"
                    name="attachments[]"
                    class="nageeb-input"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png,.webp,.zip,.doc,.docx"
                >
                @error('attachments')
                    <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </aside>
    </div>

    <div class="nageeb-sticky-actions">
        <a href="{{ route('teacher.courses.index') }}" class="nageeb-btn nageeb-btn--outline">إلغاء</a>
        <div class="nageeb-sticky-actions__primary">
            <button type="submit" name="save_action" value="draft" class="nageeb-btn nageeb-btn--outline">حفظ كمسودة</button>
            <button type="submit" name="save_action" value="continue" class="nageeb-btn nageeb-btn--primary">حفظ ومتابعة</button>
        </div>
    </div>
</form>
