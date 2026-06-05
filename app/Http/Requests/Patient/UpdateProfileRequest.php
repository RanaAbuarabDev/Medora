<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $userId = auth()->id(); 

        return [
            // استخدام sometimes يعني أن الحقل مطلوب "فقط في حال تم إرساله"
            'name'             => 'sometimes|string|max:255',
            'phone'            => 'sometimes|string|max:20',
            'email'            => 'sometimes|email|unique:users,email,' . $userId,
            'birth_date'       => 'sometimes|date|before:today',
            'gender'           => 'sometimes|string|in:male,female,ذكر,أنثى',
            
            // هذه الحقول تبقى كما هي لأنها اختيارية بطبيعتها بالواجهة
            'medical_notes'    => 'nullable|string|max:1000', 
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password'     => 'nullable|string|min:8|confirmed', 
        ];
    }
}