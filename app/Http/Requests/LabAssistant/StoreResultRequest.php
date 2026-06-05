<?php

namespace App\Http\Requests\LabTech;

use Illuminate\Foundation\Http\FormRequest;

class StoreResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'results'                => 'required|array|min:1',
            'results.*.parameter_id' => 'required|exists:test_parameters,id',
            'results.*.value'        => 'required|numeric',
            'technician_notes'       => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'results.required'                => 'مصفوفة النتائج الطبية لا يمكن أن تكون فارغة.',
            'results.*.parameter_id.required' => 'معرف الفحص الفرعي مطلوب لكل حقل.',
            'results.*.parameter_id.exists'   => 'الفحص الفرعي المحدد غير موجود بقاعدة البيانات.',
            'results.*.value.required'        => 'يرجى إدخال القيمة الرقمية للتحليل.',
            'results.*.value.numeric'         => 'القيم الطبية المدخلة يجب أن تكون أرقاماً فقط.'
        ];
    }
}