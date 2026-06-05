<?php

namespace App\Http\Requests\LabTech;

use Illuminate\Foundation\Http\FormRequest;

class SearchSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تفعيل الصلاحية (متحكم بها عبر الـ Middleware)
    }

    public function rules(): array
    {
        return [
            'sample_code' => 'required|string|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'sample_code.required' => 'يرجى إدخال أو مسح رقم العينة أولاً.',
            'sample_code.string'   => 'رقم العينة يجب أن يكون نصاً صالحاً.'
        ];
    }
}