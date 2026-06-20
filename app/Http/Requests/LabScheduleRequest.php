<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabScheduleRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم (مدير المختبر فقط)
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_manager');
    }

    /**
     * قواعد التحقق الخاصة بالمصفوفة الأسبوعية كاملة
     */
    public function rules(): array
    {
        // داخل دالة rules() في ملف LabScheduleRequest.php
        return [
            'slot_interval'           => 'nullable|integer|in:15,30,45,60', // ⚡ فحص حقل مدة الموعد الحقيقي
            'schedules'               => 'required|array|min:1|max:7',
            'schedules.*.day_of_week' => 'required|integer|between:0,6',
            'schedules.*.is_day_off'  => 'required|boolean',
            'schedules.*.start_time'  => 'required_if:schedules.*.is_day_off,false|nullable|date_format:H:i:s',
            'schedules.*.end_time'    => 'required_if:schedules.*.is_day_off,false|nullable|date_format:H:i:s|after:schedules.*.start_time',
        ];
    }

    /**
     * تخصيص رسائل الخطأ لتظهر بشكل واضح للفرونت إند
     */
    public function messages(): array
    {
        return [
            'schedules.required'     => 'يجب إرسال جدول المواعيد.',
            'schedules.array'        => 'صيغة الجدول المرسل غير صحيحة.',
            'schedules.*.end_time.after' => 'وقت الانتهاء يجب أن يكون بعد وقت البداية لكل يوم عمل.',
            'schedules.*.start_time.required_if' => 'وقت البداية مطلوب إذا لم يكن اليوم عطلة.',
            'schedules.*.end_time.required_if'   => 'وقت الانتهاء مطلوب إذا لم يكن اليوم عطلة.',
        ];
    }
}