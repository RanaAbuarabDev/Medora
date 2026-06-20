<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class LabStaffUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_manager');
    }

    public function rules(): array
    {
        // جلب معرف الموظف القادم من مسار الـ Route لعمل استثناء (Ignore) في الإيميل
        $staffId = $this->route('id');

        return [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staffId,
            'phone' => 'nullable|string',
            'role'  => 'required|string|in:lab_assistant,receptionist', // قبول الأدوار المتاحة بالمخبر فقط
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل من قِبل موظف آخر.',
            'phone.unique' => 'رقم الهاتف مسجل بالفعل لموظف آخر.',
        ];
    }
}