<?php

namespace App\Http\Requests\Teacher;

use App\Enums\ContentStatus;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $this->user()?->isTeacher()
            && $course instanceof Course
            && $course->isOwnedBy($this->user()->id);
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('save_action') === 'draft') {
            $this->merge(['status' => ContentStatus::Draft->value]);
        }

        if ($this->input('save_action') === 'publish') {
            $this->merge(['status' => ContentStatus::Live->value]);
        }

        $this->merge([
            'is_preview' => $this->boolean('is_preview'),
            'region_scope' => $this->input('region_scope', 'all'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Course $course */
        $course = $this->route('course');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->whereIn(
                    'semester_id',
                    $course->semesters()->select('id'),
                ),
            ],
            'region_scope' => ['required', Rule::in(['all', 'selected'])],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer', Rule::exists('regions', 'id')],
            'status' => ['required', Rule::in([ContentStatus::Draft->value, ContentStatus::Live->value])],
            'is_preview' => ['required', 'boolean'],
            'estimated_duration' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'videos' => ['nullable', 'array', 'max:10'],
            'videos.*' => ['file', 'mimes:mp4,webm,mov,avi', 'max:102400'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,zip,doc,docx,mp3,wav'],
            'save_action' => ['nullable', Rule::in(['draft', 'save', 'publish'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الدرس مطلوب.',
            'unit_id.required' => 'يجب اختيار الوحدة.',
            'unit_id.exists' => 'الوحدة المختارة لا تنتمي إلى هذه المادة.',
            'status.required' => 'حالة التوفر مطلوبة.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان الدرس',
            'description' => 'الوصف',
            'unit_id' => 'الوحدة',
            'status' => 'التوفر',
            'is_preview' => 'معاينة مجانية',
            'videos' => 'فيديوهات الدرس',
            'attachments' => 'المرفقات',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function lessonAttributes(): array
    {
        return $this->safe()->only([
            'title',
            'description',
            'unit_id',
            'status',
            'is_preview',
            'estimated_duration',
        ]);
    }
}
