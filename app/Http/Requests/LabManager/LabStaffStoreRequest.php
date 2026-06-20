<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class LabStaffStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // متاح فقط لمدير المختبر
        return auth()->user()->hasRole('lab_manager');
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email', // التأكد من عدم تكرار الإيميل
            'phone'    => 'required|string|unique:users,phone', // التأكد من عدم تكرار الهاتف
            'password' => 'required|string|min:8', // كلمة المرور الافتراضية للموظف الجديد
            'role'     => 'required|string|in:lab_assistant,receptionist', // حصر الأدوار المتاحة بالواجهة
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'اسم الموظف مطلوب.',
            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.unique'      => 'البريد الإلكتروني مستخدم بالفعل.',
            'phone.required'    => 'رقم الهاتف مطلوب.',
            'phone.unique'      => 'رقم الهاتف مسجل بالفعل لموظف آخر.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min'      => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'role.required'     => 'يجب تحديد الدور الوظيفي للموظف.',
            'role.in'           => 'الدور الوظيفي المحدد غير صالح.',
        ];
    }
}