<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'master_test_id' => 'required|exists:master_tests,id',
            'price'          => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'master_test_id.required' => 'يجب اختيار تحليل من القائمة الرئيسية.',
            'master_test_id.exists'   => 'التحليل المختار غير موجود في البنك المركزي.',
            'price.required'          => 'يرجى تحديد سعر التحليل في مخبركم.',
            'price.numeric'           => 'يجب أن يكون السعر رقماً.',
        ];
    }
}
