<?php

namespace App\Http\Requests\Teacher;

use App\Enums\CourseStatus;
use App\Enums\GradeLevel;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()?->isTeacher()) {
            return false;
        }

        $course = $this->route('course');

        if ($course instanceof Course) {
            return $course->isOwnedBy($this->user()->id);
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('save_action') === 'draft') {
            $this->merge(['status' => CourseStatus::Draft->value]);
        }

        $price = $this->input('reference_price');

        $this->merge([
            'is_free' => $this->boolean('is_free'),
            'reference_price' => $price === '' || $price === null ? null : $price,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'grade_level' => ['required', Rule::enum(GradeLevel::class)],
            'status' => ['required', Rule::enum(CourseStatus::class)],
            'is_free' => ['required', 'boolean'],
            'reference_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'attachments' => ['nullable', 'array', 'max:8'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,zip,doc,docx'],
            'save_action' => ['nullable', Rule::in(['draft', 'continue'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان المادة مطلوب.',
            'grade_level.required' => 'الصف الدراسي مطلوب.',
            'status.required' => 'حالة التوفر مطلوبة.',
            'cover_image.image' => 'يجب أن يكون الغلاف صورة.',
            'cover_image.max' => 'حجم صورة الغلاف يجب ألا يتجاوز 2 ميغابايت.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function courseAttributes(): array
    {
        return $this->safe()->only([
            'title',
            'description',
            'grade_level',
            'status',
            'is_free',
            'reference_price',
        ]);
    }
}
