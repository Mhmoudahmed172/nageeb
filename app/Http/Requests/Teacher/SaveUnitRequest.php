<?php

namespace App\Http\Requests\Teacher;

use App\Enums\ContentStatus;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUnitRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $course = $this->route('course');
        if (! $this->filled('semester_id') && $course instanceof Course) {
            $this->merge(['semester_id' => $course->defaultSemester()->id]);
        }
    }

    public function authorize(): bool
    {
        $course = $this->route('course');

        return $this->user()?->isTeacher()
            && $course instanceof Course
            && $course->isOwnedBy($this->user()->id);
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
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'semester_id' => [
                'required',
                'integer',
                Rule::exists('semesters', 'id')->where('course_id', $course->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'اسم الوحدة مطلوب.',
            'status.required' => 'حالة الوحدة مطلوبة.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'اسم الوحدة',
            'description' => 'وصف الوحدة',
            'status' => 'حالة الوحدة',
        ];
    }
}
