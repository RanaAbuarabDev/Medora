<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * تحديد إذا كان المستخدم مخولاً لإرسال هذا الطلب.
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
            
          
            'test_ids'         => 'required|array|min:1',
            
            'test_ids.*'       => 'required|integer|exists:lab_tests,id', 
            
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today', 
            ],
            'start_time'       => [
                'required',
                'date_format:H:i:s', 
            ],
        ];
    }

    /**
     * تخصيص رسائل الخطأ بالعربي لتظهر للمريض بشكل واضح.
     */
    public function messages(): array
    {
        return [
            'lab_id.required'                 => 'يجب تحديد المختبر المطلوب.',
            'lab_id.exists'                   => 'المختبر المختار غير موجود.',
            
            
            'test_ids.required'               => 'يرجى اختيار تحليل واحد على الأقل لإتمام الحجز.',
            'test_ids.array'                  => 'صيغة التحاليل المرسلة غير صحيحة.',
            'test_ids.min'                    => 'يجب اختيار تحليل واحد على الأقل.',
            'test_ids.*.exists'               => 'أحد التحاليل المحددة غير متوفر في النظام.',
            
            'appointment_date.required'       => 'تاريخ الحجز ضروري جداً.',
            'appointment_date.date'           => 'صيغة التاريخ غير صحيحة.',
            'appointment_date.after_or_equal' => 'لا يمكنك الحجز في تاريخ مضى.',
            'start_time.required'             => 'يرجى اختيار الوقت المتاح.',
            'start_time.date_format'          => 'صيغة الوقت غير صحيحة، يجب أن تكون (ساعة:دقيقة:ثانية).',
        ];
    }
}