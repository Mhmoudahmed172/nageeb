@props(['courses', 'regions', 'exam' => null])

@php
    use App\Enums\ExamStatus;

    $isEdit = $exam !== null;
    $action = $isEdit ? route('teacher.exams.update', $exam) : route('teacher.exams.store');

    $tree = $courses->map(fn ($course) => [
        'id' => $course->id,
        'semesters' => $course->semesters->map(fn ($semester) => [
            'id' => $semester->id,
            'title' => $semester->title,
            'units' => $semester->units->map(fn ($unit) => [
                'id' => $unit->id,
                'title' => $unit->title,
                'lessons' => $unit->lessons->map(fn ($lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                ])->values(),
            ])->values(),
        ])->values(),
    ])->values();

    $selected = [
        'course' => (string) old('course_id', $exam?->course_id ?? $courses->first()?->id),
        'semester' => (string) old('semester_id', $exam?->semester_id),
        'unit' => (string) old('unit_id', $exam?->unit_id),
        'lesson' => (string) old('lesson_id', $exam?->lesson_id),
    ];

    $regionScope = old('region_scope', ($exam && $exam->regions->isNotEmpty()) ? 'selected' : 'all');
    $selectedRegionIds = array_map('intval', (array) old('region_ids', $exam?->regions->pluck('id')->all() ?? []));
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="grid gap-6"
    enctype="multipart/form-data"
    x-data="{
        tree: {{ Illuminate\Support\Js::from($tree) }},
        courseId: '{{ $selected['course'] }}',
        semesterId: '{{ $selected['semester'] }}',
        unitId: '{{ $selected['unit'] }}',
        lessonId: '{{ $selected['lesson'] }}',
        regionScope: '{{ $regionScope }}',
        deliveryMode: '{{ old('delivery_mode', $exam?->delivery_mode->value ?? \App\Enums\ExamDeliveryMode::Interactive->value) }}',
        get semesters() {
            return this.tree.find((course) => String(course.id) === String(this.courseId))?.semesters ?? [];
        },
        get units() {
            return this.semesters.find((semester) => String(semester.id) === String(this.semesterId))?.units ?? [];
        },
        get lessons() {
            return this.units.find((unit) => String(unit.id) === String(this.unitId))?.lessons ?? [];
        },
        onCourseChange() { this.semesterId = ''; this.unitId = ''; this.lessonId = ''; },
        onSemesterChange() { this.unitId = ''; this.lessonId = ''; },
        onUnitChange() { this.lessonId = ''; },
    }"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="nageeb-card grid gap-5">
        <h2 class="nageeb-title-section">تفاصيل الاختبار</h2>

        <x-form-input label="اسم الاختبار" name="title" required :value="$exam?->title" maxlength="255" />
        <x-form-textarea label="الوصف" name="description" :value="$exam?->description" rows="4" />

        <fieldset class="nageeb-field">
            <legend class="nageeb-label">نوع الاختبار <span class="text-alert">*</span></legend>
            <div class="grid gap-2">
                <label class="flex items-center gap-2">
                    <input type="radio" name="delivery_mode" value="{{ \App\Enums\ExamDeliveryMode::Interactive->value }}" x-model="deliveryMode">
                    <span>اختبار تفاعلي داخل المنصة</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="delivery_mode" value="{{ \App\Enums\ExamDeliveryMode::File->value }}" x-model="deliveryMode">
                    <span>اختبار / ورقة اختبار مرفوعة كملف</span>
                </label>
            </div>
            @error('delivery_mode')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </fieldset>

        <div class="nageeb-field" x-show="deliveryMode === '{{ \App\Enums\ExamDeliveryMode::File->value }}'" x-cloak>
            <label class="nageeb-label" for="exam_file">ملف الاختبار {{ $exam?->hasPaperFile() ? '' : '*' }}</label>
            <input
                id="exam_file"
                type="file"
                name="file"
                class="nageeb-input"
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
            >
            @if ($exam?->hasPaperFile())
                <p class="nageeb-field-help mt-1">
                    الملف الحالي: {{ $exam->file_original_name }}
                    — اترك الحقل فارغًا للإبقاء عليه.
                </p>
            @else
                <p class="nageeb-field-help mt-1">PDF أو Word أو صورة. الحد الأقصى 50 ميغابايت.</p>
            @endif
            @error('file')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="nageeb-card grid gap-5">
        <div>
            <h2 class="nageeb-title-section">موضع الاختبار في المحتوى</h2>
            <p class="nageeb-text-muted text-sm mt-1">اختر المادة على الأقل. كل مستوى أدق يجعل الاختبار مرتبطًا بمحتوى أضيق.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="nageeb-field">
                <label class="nageeb-label" for="course_id">المادة <span class="text-alert">*</span></label>
                <select id="course_id" name="course_id" class="nageeb-select" x-model="courseId" @change="onCourseChange()" required>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
                @error('course_id')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="nageeb-field">
                <label class="nageeb-label" for="semester_id">الفصل الدراسي</label>
                <select id="semester_id" name="semester_id" class="nageeb-select" x-model="semesterId" @change="onSemesterChange()">
                    <option value="">كل الفصول</option>
                    <template x-for="semester in semesters" :key="semester.id">
                        <option :value="semester.id" x-text="semester.title"></option>
                    </template>
                </select>
                @error('semester_id')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="nageeb-field">
                <label class="nageeb-label" for="unit_id">الوحدة</label>
                <select id="unit_id" name="unit_id" class="nageeb-select" x-model="unitId" @change="onUnitChange()">
                    <option value="">كل الوحدات</option>
                    <template x-for="unit in units" :key="unit.id">
                        <option :value="unit.id" x-text="unit.title"></option>
                    </template>
                </select>
                @error('unit_id')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="nageeb-field">
                <label class="nageeb-label" for="lesson_id">الدرس</label>
                <select id="lesson_id" name="lesson_id" class="nageeb-select" x-model="lessonId">
                    <option value="">كل الدروس</option>
                    <template x-for="lesson in lessons" :key="lesson.id">
                        <option :value="lesson.id" x-text="lesson.title"></option>
                    </template>
                </select>
                @error('lesson_id')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="nageeb-card grid gap-5">
        <h2 class="nageeb-title-section">إعدادات الاختبار</h2>

        <div class="grid gap-5 sm:grid-cols-3">
            <x-form-input label="مدة الاختبار (دقيقة)" name="duration_minutes" type="number" min="1" max="600" :value="$exam?->duration_minutes" help="اتركه فارغًا لاختبار بلا وقت." />
            <x-form-input label="عدد المحاولات" name="max_attempts" type="number" min="1" max="20" required :value="$exam?->max_attempts ?? 1" />
            <x-form-input label="درجة النجاح (%)" name="passing_score" type="number" min="0" max="100" step="0.01" required :value="$exam ? (float) $exam->passing_score : 50" />
        </div>

        <div class="nageeb-field">
            <label class="nageeb-label" for="status">حالة الاختبار <span class="text-alert">*</span></label>
            <select id="status" name="status" class="nageeb-select" required>
                @foreach (ExamStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('status', $exam?->status->value ?? ExamStatus::Draft->value) === $case->value)>
                        {{ $case->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-3" x-show="deliveryMode !== '{{ \App\Enums\ExamDeliveryMode::File->value }}'">
            @foreach ([
                'show_results_immediately' => 'إظهار النتيجة مباشرة بعد التسليم',
                'show_correct_answers' => 'إظهار الإجابات الصحيحة للطالب',
                'shuffle_questions' => 'ترتيب عشوائي للأسئلة',
                'shuffle_options' => 'ترتيب عشوائي للإجابات',
            ] as $field => $label)
                <label class="nageeb-toggle">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input
                        type="checkbox"
                        name="{{ $field }}"
                        value="1"
                        class="nageeb-checkbox"
                        @checked(old($field, $exam?->{$field} ?? ($field === 'show_results_immediately')))
                    >
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </section>

    <section class="nageeb-card grid gap-4">
        <div>
            <h2 class="nageeb-title-section">إتاحة الاختبار حسب المنطقة</h2>
            <p class="nageeb-text-muted text-sm mt-1">تتبع نفس قواعد إتاحة محتوى الدروس: بدون تحديد يعني متاح لكل المناطق.</p>
        </div>

        <label class="flex items-center gap-2">
            <input type="radio" name="region_scope" value="all" x-model="regionScope">
            <span>جميع المناطق</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="radio" name="region_scope" value="selected" x-model="regionScope">
            <span>مناطق محددة</span>
        </label>

        <div class="grid gap-2 ps-6" x-show="regionScope === 'selected'" x-cloak x-transition.opacity>
            @forelse ($regions as $region)
                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="region_ids[]"
                        value="{{ $region->id }}"
                        class="nageeb-checkbox"
                        @checked(in_array($region->id, $selectedRegionIds, true))
                    >
                    <span>{{ $region->name }}</span>
                </label>
            @empty
                <p class="nageeb-caption">لا توجد مناطق مفعّلة حاليًا.</p>
            @endforelse
        </div>
    </section>

    <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('teacher.exams.index') }}" class="nageeb-btn nageeb-btn--ghost">إلغاء</a>
        <x-button type="submit">{{ $isEdit ? 'حفظ الاختبار' : 'إنشاء الاختبار' }}</x-button>
    </div>
</form>
