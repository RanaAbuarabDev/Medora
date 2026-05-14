<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * تحديد إذا كان المستخدم مخولاً لإرسال هاد الطلب.
     * بما إننا عم نتعامل مع مريض مسجل دخول، منرجعها true.
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * شروط التحقق من البيانات المرسلة.
     */
    public function rules(): array
    {
        return [
            'lab_id'           => 'required|exists:laboratories,id',
            'test_id'          => 'required|exists:lab_tests,id', // تأكدي إن اسم جدول التحاليل tests
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today', // ممنوع يحجز بتاريخ قديم
            ],
            'start_time'       => [
                'required',
                'date_format:H:i', // نطلب الوقت بصيغة 24 ساعة مثل 09:30
            ],
        ];
    }

    /**
     * تخصيص رسائل الخطأ بالعربي لتظهر للمريض بشكل واضح.
     */
    public function messages(): array
    {
        return [
            'lab_id.required'           => 'يجب تحديد المختبر المطلوب.',
            'lab_id.exists'             => 'المختبر المختار غير موجود.',
            'test_id.required'          => 'يرجى اختيار التحليل المطلوب إجراءه.',
            'appointment_date.required' => 'تاريخ الحجز ضروري جداً.',
            'appointment_date.after_or_equal' => 'لا يمكنك الحجز في تاريخ مضى.',
            'start_time.required'       => 'يرجى اختيار الوقت المتاح.',
            'start_time.date_format'    => 'صيغة الوقت غير صحيحة.',
        ];
    }
}