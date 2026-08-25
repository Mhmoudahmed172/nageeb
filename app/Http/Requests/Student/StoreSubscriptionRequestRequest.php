<?php

namespace App\Http\Requests\Student;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequestRequest extends FormRequest
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
        /** @var Course $course */
        $course = $this->route('course');

        return [
            'package_id' => [
                'required',
                'integer',
                Rule::exists('subscription_packages', 'id')->where('course_id', $course->id),
            ],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'package_id.required' => 'يرجى اختيار باقة.',
            'package_id.exists' => 'الباقة المختارة غير صالحة لهذه المادة.',
            'receipt.required' => 'صورة إيصال الدفع مطلوبة.',
            'receipt.mimes' => 'يجب أن يكون الإيصال بصيغة jpg أو png أو pdf.',
            'receipt.max' => 'حجم ملف الإيصال يجب ألا يتجاوز 5 ميغابايت.',
        ];
    }
}
