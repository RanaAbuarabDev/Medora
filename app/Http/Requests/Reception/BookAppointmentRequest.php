<?php

namespace App\Http\Requests\Reception;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مخولاً لتنفيذ هذا الطلب
     */
    public function authorize(): bool
    {
        return true; // تفعيل الطلب
    }

    /**
     * قواعد التحقق الخاصة بحجز الموعد (تراعي المريض القديم والجديد)
     */
    public function rules(): array
    {
        return [
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time'       => 'required|string',
            'total_amount'     => 'required|numeric|min:0',
            'test_ids'         => 'required|array|min:1',
            'test_ids.*'       => 'exists:lab_tests,id', // التأكد أن المعرفات موجودة فعلياً في جدول التحاليل
            'confirm_cash'     => 'nullable|boolean', // الـ Checkbox الخاص بقبض الكاش فوراً من الواجهة
            'is_fasting'       => 'nullable|boolean', // الـ Checkbox الخاص بـ "المريض صائم"

            // شروط مرنة: إذا لم يتم إرسال patient_id تصبح بيانات المريض الجديد إجبارية
            'patient_id'         => 'nullable|exists:users,id',
            'patient_name'       => 'required_without:patient_id|string|max:255',
            'phone'              => 'required_without:patient_id|string',
            'gender'             => 'required_without:patient_id|in:male,female',
            'birth_date'         => 'required_without:patient_id|date|before:today',
            'emergency_phone'    => 'nullable|string',
            'address'            => 'nullable|string|max:500',
        ];
    }

    
    public function messages(): array
    {
        return [
            'appointment_date.required' => 'تاريخ الموعد مطلوب.',
            'appointment_date.after_or_equal' => 'لا يمكن حجز موعد في تاريخ قد مضى.',
            'start_time.required'       => 'وقت الموعد مطلوب.',
            'test_ids.required'         => 'يجب اختيار تحليل واحد على الأقل.',
            'patient_name.required_without' => 'اسم المريض الجديد مطلوب في حال عدم اختيار مريض مسجل.',
            'phone.required_without'        => 'رقم هاتف المريض الجديد مطلوب.',
            'gender.required_without'       => 'جنس المريض مطلوب.',
            'birth_date.required_without'   => 'تاريخ ميلاد المريض مطلوب.',
        ];
    }
}