<?php

namespace App\Http\Requests\Teacher;

use App\Enums\ExamDeliveryMode;
use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->isTeacher()) {
            return false;
        }

        // Ownership is checked before validation so a foreign exam id never leaks
        // through a validation error message.
        $exam = $this->route('exam');

        return ! $exam instanceof Exam || $exam->isOwnedBy($user->id);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_results_immediately' => $this->boolean('show_results_immediately'),
            'show_correct_answers' => $this->boolean('show_correct_answers'),
            'shuffle_questions' => $this->boolean('shuffle_questions'),
            'shuffle_options' => $this->boolean('shuffle_options'),
            'delivery_mode' => $this->input('delivery_mode', ExamDeliveryMode::Interactive->value),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $teacherId = $this->user()->id;
        $ownedCourses = fn ($query) => $query->where('teacher_id', $teacherId);

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where('teacher_id', $teacherId),
            ],
            'semester_id' => [
                'nullable',
                'integer',
                Rule::exists('semesters', 'id')->whereIn(
                    'course_id',
                    \App\Models\Course::query()->where('teacher_id', $teacherId)->select('id'),
                ),
            ],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->whereIn(
                    'semester_id',
                    \App\Models\Semester::query()->whereIn(
                        'course_id',
                        \App\Models\Course::query()->where('teacher_id', $teacherId)->select('id'),
                    )->select('id'),
                ),
            ],
            'lesson_id' => [
                'nullable',
                'integer',
                Rule::exists('lessons', 'id')->whereIn(
                    'unit_id',
                    Unit::query()->whereIn(
                        'semester_id',
                        \App\Models\Semester::query()->whereIn(
                            'course_id',
                            \App\Models\Course::query()->where('teacher_id', $teacherId)->select('id'),
                        )->select('id'),
                    )->select('id'),
                ),
            ],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::enum(ExamStatus::class)],
            'delivery_mode' => ['required', Rule::enum(ExamDeliveryMode::class)],
            'file' => [
                Rule::requiredIf(fn () => $this->isFileExam() && ! $this->existingExam()?->hasPaperFile()),
                'nullable',
                'file',
            ],
            'show_results_immediately' => ['required', 'boolean'],
            'show_correct_answers' => ['required', 'boolean'],
            'shuffle_questions' => ['required', 'boolean'],
            'shuffle_options' => ['required', 'boolean'],
            'region_scope' => ['required', Rule::in(['all', 'selected'])],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer', Rule::exists('regions', 'id')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $placement = $this->placement();

            if (! $this->isFileExam() && $this->file('file')) {
                $validator->errors()->add('file', 'اختبار تفاعلي لا يقبل رفع ملف. اختر «اختبار مرفق» أولًا.');
            }

            if ($this->isFileExam() && $this->file('file')) {
                try {
                    app(\App\Support\MediaStore::class)->assertSafe(
                        $this->file('file'),
                        \App\Support\MediaStore::KIND_EXAM,
                    );
                } catch (\Illuminate\Validation\ValidationException $exception) {
                    foreach ($exception->errors() as $key => $messages) {
                        foreach ($messages as $message) {
                            $validator->errors()->add($key, $message);
                        }
                    }
                }
            }

            if ($placement['lesson_id'] === null) {
                return;
            }

            $lesson = Lesson::query()->with('unit.semester')->find($placement['lesson_id']);

            if ($lesson && (int) $lesson->unit->semester->course_id !== (int) $this->input('course_id')) {
                $validator->errors()->add('lesson_id', 'الدرس المختار لا ينتمي إلى هذه المادة.');
            }
        });
    }

    /**
     * Placement is normalised downwards: choosing a lesson also pins its unit and
     * semester so the hierarchy stored on the exam is always internally consistent.
     *
     * @return array{semester_id: ?int, unit_id: ?int, lesson_id: ?int}
     */
    public function placement(): array
    {
        $lessonId = $this->filled('lesson_id') ? (int) $this->input('lesson_id') : null;
        $unitId = $this->filled('unit_id') ? (int) $this->input('unit_id') : null;
        $semesterId = $this->filled('semester_id') ? (int) $this->input('semester_id') : null;

        if ($lessonId) {
            $lesson = Lesson::query()->with('unit')->find($lessonId);
            $unitId = $lesson?->unit_id ?? $unitId;
            $semesterId = $lesson?->unit?->semester_id ?? $semesterId;
        } elseif ($unitId) {
            $semesterId = Unit::query()->find($unitId)?->semester_id ?? $semesterId;
        }

        return [
            'semester_id' => $semesterId,
            'unit_id' => $unitId,
            'lesson_id' => $lessonId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function examAttributes(): array
    {
        return [
            ...$this->safe()->only([
                'title',
                'description',
                'course_id',
                'duration_minutes',
                'max_attempts',
                'passing_score',
                'status',
                'show_results_immediately',
                'show_correct_answers',
                'shuffle_questions',
                'shuffle_options',
                'delivery_mode',
            ]),
            ...$this->placement(),
        ];
    }

    public function isFileExam(): bool
    {
        return $this->input('delivery_mode') === ExamDeliveryMode::File->value;
    }

    public function existingExam(): ?Exam
    {
        $exam = $this->route('exam');

        return $exam instanceof Exam ? $exam : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function paperFileAttributes(): ?array
    {
        $file = $this->file('file');

        if (! $this->isFileExam() || ! $file) {
            return null;
        }

        $stored = app(\App\Support\MediaStore::class)->store($file, \App\Support\MediaStore::KIND_EXAM);

        return [
            'file_disk' => $stored['disk'],
            'file_path' => $stored['path'],
            'file_original_name' => $stored['original_name'],
            'file_mime' => $stored['mime'],
            'file_size' => $stored['size'],
            'file_uploaded_at' => now(),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function regionIds(): array
    {
        return $this->validated('region_scope') === 'selected'
            ? array_map('intval', $this->validated('region_ids') ?? [])
            : [];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'اسم الاختبار',
            'course_id' => 'المادة',
            'semester_id' => 'الفصل الدراسي',
            'unit_id' => 'الوحدة',
            'lesson_id' => 'الدرس',
            'duration_minutes' => 'مدة الاختبار',
            'max_attempts' => 'عدد المحاولات',
            'passing_score' => 'درجة النجاح',
            'status' => 'حالة الاختبار',
            'delivery_mode' => 'نوع الاختبار',
            'file' => 'ملف الاختبار',
        ];
    }
}
