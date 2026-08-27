<?php

namespace App\Http\Requests\Teacher;

use App\Enums\ContentStatus;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveAccessPlanRequest extends FormRequest
{
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
            'access_duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'semester_ids' => ['required', 'array', 'min:1'],
            'semester_ids.*' => [
                'integer',
                Rule::exists('semesters', 'id')->where('course_id', $course->id),
            ],
            'prices' => ['required', 'array'],
            'prices.*.region_id' => ['required', 'integer', Rule::exists('regions', 'id')],
            'prices.*.price' => ['required', 'numeric', 'min:0'],
            'prices.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $regionIds = collect($this->input('prices', []))->pluck('region_id');
            if ($regionIds->count() !== $regionIds->unique()->count()) {
                $validator->errors()->add('prices', 'لا يمكن تكرار سعر نفس المنطقة لنفس الخطة.');
            }
        });
    }
}
