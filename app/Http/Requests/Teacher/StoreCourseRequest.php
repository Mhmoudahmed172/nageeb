<?php

namespace App\Http\Requests\Teacher;

use App\Enums\CourseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'grade_level' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::enum(CourseStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'اسم المادة مطلوب.',
            'status.required' => 'حالة المادة مطلوبة.',
        ];
    }
}
