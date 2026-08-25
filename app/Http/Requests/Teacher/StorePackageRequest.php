<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'price_gaza' => ['required', 'numeric', 'min:0'],
            'price_west_bank_abroad' => ['required', 'numeric', 'min:0'],
            'duration_label' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الباقة مطلوب.',
            'price_gaza.required' => 'سعر غزة مطلوب.',
            'price_west_bank_abroad.required' => 'سعر الضفة والخارج مطلوب.',
            'duration_label.required' => 'مدة الباقة مطلوبة.',
        ];
    }
}
