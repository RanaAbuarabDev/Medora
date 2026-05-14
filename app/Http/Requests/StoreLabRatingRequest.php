<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabRatingRequest extends FormRequest
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
            'lab_id' => 'required|exists:laboratories,id',
            'appointment_id' => 'required|exists:appointments,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.exists' => 'عذراً، رقم الحجز هذا غير موجود في سجلاتنا.',
            'rating.max' => 'التقييم يجب أن يكون بين 1 و 5 نجوم.',
        ];
    }
}
