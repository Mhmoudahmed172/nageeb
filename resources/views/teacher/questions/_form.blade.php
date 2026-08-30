@props(['courses', 'types', 'difficulties', 'question' => null])

@php
    use App\Enums\QuestionType;

    $isEdit = $question !== null;
    $action = $isEdit ? route('teacher.questions.update', $question) : route('teacher.questions.store');

    $oldOptions = old('options');
    $oldCorrect = array_map('intval', (array) old('correct_options', []));

    if (is_array($oldOptions)) {
        $initialOptions = collect($oldOptions)->values()->map(fn ($option, $index) => [
            'text' => (string) ($option['text'] ?? ''),
            'correct' => in_array($index, $oldCorrect, true),
        ])->all();
    } elseif ($isEdit) {
        $initialOptions = $question->options->map(fn ($option) => [
            'text' => $option->text,
            'correct' => (bool) $option->is_correct,
        ])->values()->all();
    } else {
        $initialOptions = [
            ['text' => '', 'correct' => true],
            ['text' => '', 'correct' => false],
        ];
    }
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="grid gap-6"
    x-data="{
        type: '{{ old('type', $question?->type->value ?? QuestionType::MultipleChoice->value) }}',
        options: {{ Illuminate\Support\Js::from($initialOptions) }},
        get allowsMultiple() { return this.type === '{{ QuestionType::MultipleResponse->value }}'; },
        get isTrueFalse() { return this.type === '{{ QuestionType::TrueFalse->value }}'; },
        onTypeChange() {
            if (this.isTrueFalse) {
                this.options = [
                    { text: 'صح', correct: true },
                    { text: 'خطأ', correct: false },
                ];
                return;
            }

            if (! this.allowsMultiple) {
                let seen = false;
                this.options.forEach((option) => {
                    if (option.correct && ! seen) { seen = true; return; }
                    option.correct = false;
                });
                if (! seen && this.options.length) { this.options[0].correct = true; }
            }
        },
        toggle(index) {
            if (this.allowsMultiple) {
                this.options[index].correct = ! this.options[index].correct;
                return;
            }

            this.options.forEach((option, current) => { option.correct = current === index; });
        },
        addOption() { if (! this.isTrueFalse && this.options.length < 10) { this.options.push({ text: '', correct: false }); } },
        removeOption(index) { if (! this.isTrueFalse && this.options.length > 2) { this.options.splice(index, 1); } },
    }"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="nageeb-card grid gap-5">
        <h2 class="nageeb-title-section">السؤال</h2>

        <div class="nageeb-field">
            <label class="nageeb-label" for="type">نوع السؤال <span class="text-alert">*</span></label>
            <select id="type" name="type" class="nageeb-select" x-model="type" @change="onTypeChange()" required>
                @foreach ($types as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
            @error('type')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <x-form-textarea label="نص السؤال" name="text" required :value="$question?->text" rows="3" />
        <x-form-textarea label="شرح الإجابة (اختياري)" name="explanation" :value="$question?->explanation" rows="2" />
    </section>

    <section class="nageeb-card grid gap-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="nageeb-title-section">الخيارات والإجابة الصحيحة</h2>
                <p class="nageeb-text-muted text-sm mt-1" x-text="allowsMultiple ? 'يمكن تحديد أكثر من إجابة صحيحة.' : 'حدّد إجابة صحيحة واحدة.'"></p>
            </div>
            <button type="button" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm" @click="addOption()" x-show="! isTrueFalse">+ خيار</button>
        </div>

        @error('options')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        @error('correct_options')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror

        <template x-for="(option, index) in options" :key="index">
            <div class="flex items-center gap-3 border border-border rounded-md p-3">
                <input
                    type="checkbox"
                    class="nageeb-checkbox"
                    name="correct_options[]"
                    :value="index"
                    :checked="option.correct"
                    @change="toggle(index)"
                    :aria-label="'تحديد الخيار ' + (index + 1) + ' كإجابة صحيحة'"
                >
                <input
                    type="text"
                    class="nageeb-input flex-1"
                    :name="'options[' + index + '][text]'"
                    x-model="option.text"
                    maxlength="1000"
                    placeholder="نص الخيار"
                    required
                >
                <button
                    type="button"
                    class="nageeb-btn nageeb-btn--ghost nageeb-btn--sm text-danger"
                    @click="removeOption(index)"
                    x-show="! isTrueFalse && options.length > 2"
                >حذف</button>
            </div>
        </template>
    </section>

    <section class="nageeb-card grid gap-5 sm:grid-cols-2">
        <x-form-input label="الدرجة" name="points" type="number" step="0.25" min="0.25" max="100" required :value="$question ? (float) $question->points : 1" />

        <div class="nageeb-field">
            <label class="nageeb-label" for="difficulty">مستوى الصعوبة <span class="text-alert">*</span></label>
            <select id="difficulty" name="difficulty" class="nageeb-select" required>
                @foreach ($difficulties as $case)
                    <option value="{{ $case->value }}" @selected(old('difficulty', $question?->difficulty->value ?? 'medium') === $case->value)>
                        {{ $case->label() }}
                    </option>
                @endforeach
            </select>
            @error('difficulty')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="nageeb-field">
            <label class="nageeb-label" for="course_id">المادة (تصنيف)</label>
            <select id="course_id" name="course_id" class="nageeb-select">
                <option value="">بدون تصنيف</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) old('course_id', $question?->course_id) === (string) $course->id)>
                        {{ $course->title }}
                    </option>
                @endforeach
            </select>
            @error('course_id')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="nageeb-field">
            <label class="nageeb-label" for="unit_id">الوحدة (تصنيف)</label>
            <select id="unit_id" name="unit_id" class="nageeb-select">
                <option value="">بدون تصنيف</option>
                @foreach ($courses as $course)
                    @foreach ($course->semesters as $semester)
                        @foreach ($semester->units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id', $question?->unit_id) === (string) $unit->id)>
                                {{ $course->title }} — {{ $unit->title }}
                            </option>
                        @endforeach
                    @endforeach
                @endforeach
            </select>
            @error('unit_id')<p class="nageeb-field-error" role="alert">{{ $message }}</p>@enderror
        </div>
    </section>

    <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('teacher.questions.index') }}" class="nageeb-btn nageeb-btn--ghost">إلغاء</a>
        <x-button type="submit">{{ $isEdit ? 'حفظ السؤال' : 'إضافة السؤال' }}</x-button>
    </div>
</form>
