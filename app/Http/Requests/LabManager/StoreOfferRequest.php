<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'start_date'          => 'required|date|after_or_equal:today',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'lab_test_id'         => 'nullable|exists:lab_tests,id',
            'category_id'         => 'nullable|exists:test_categories,id',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $labTestId = $this->input('lab_test_id');
            $categoryId = $this->input('category_id');

            if (empty($labTestId) && empty($categoryId)) {
                $validator->errors()->add('target', 'يجب ربط العرض بتحليل محدد أو بفئة كاملة.');
            }

            if (!empty($labTestId) && !empty($categoryId)) {
                $validator->errors()->add('target', 'لا يمكن ربط العرض بتحليل وفئة معاً، اختر أحدهما فقط.');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'فشل في التحقق من البيانات تراجع المدخلات',
            'errors'  => $validator->errors()
        ], 422));
    }
}