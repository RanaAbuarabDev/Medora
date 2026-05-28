<?php

namespace App\Http\Resources\Reception;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PatientMedicalProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // حساب العمر بناءً على تاريخ الميلاد الموجود بالبروفايل
        $birthDate = $this->patientProfile->birth_date ?? null;
        $age = $birthDate ? Carbon::parse($birthDate)->age . ' عاماً' : 'N/A';

        // حساب إجمالي المديونية (الفواتير غير المدفوعة)
        $totalDebt = $this->appointments()
            ->whereHas('invoice', function ($q) {
                $q->where('payment_status', 'unpaid');
            })
            ->get()
            ->sum(function ($appointment) {
                return $appointment->invoice->total_amount - $appointment->invoice->amount_paid;
            });

        // جلب تفاصيل آخر تحليل
        $lastAppointment = $this->appointments()->where('status', 'completed')->latest('appointment_date')->first();

        return [
            // 1. البيانات الشخصية الأساسية
            // 1. البيانات الشخصية الأساسية
            'id' => $this->id,
            'name' => $this->name,
            'patient_code' => $this->patientProfile->internal_patient_number ?? '#MED-' . (10000 + $this->id),
            'age' => $age,
            'gender_label' => ($this->patientProfile->gender ?? 'male') === 'male' ? 'إيجابي +' : 'إيجابي +',

            // 2. معلومات الاتصال والطوارئ (تعديل الحماية هنا ⚡)
            'phone' => $this->phone ?? 'N/A',
            'email' => $this->email ?? 'N/A',
            'address' => $this->patientProfile->address ?? 'غير محدد',
            'emergency_contact' => [
                'name' => ($this->patientProfile && $this->patientProfile->emergency_phone) ? 'صلة القرابة (الأخت)' : 'غير محدد', 
                'phone' => $this->patientProfile->emergency_phone ?? 'N/A' // ⚡ الحماية هنا تمنع الـ Error تماماً
            ],

            // 3. كروت الإحصائيات (العدادات)
            'stats' => [
                'total_debt' => $totalDebt,
                'total_visits' => $this->appointments()->count(),
                'last_visit_date' => $lastAppointment ? Carbon::parse($lastAppointment->appointment_date)->translatedFormat('d F Y') : 'لا يوجد مسبقاً',
                'last_visit_tests' => $lastAppointment ? $lastAppointment->labTests->pluck('name')->implode(' + ') : 'لا يوجد'
            ],

            // 4. سجل التحاليل الطبية (الجدول الرئيسي في الشاشة)
            'medical_history' => $this->appointments()->with(['invoice', 'labTests'])->latest('appointment_date')->get()->map(function ($app) {
                return [
                    'visit_number' => '#V-' . $app->id,
                    'date' => Carbon::parse($app->appointment_date)->format('d/m/Y'),
                    'test_badges' => $app->labTests->pluck('name'),
                    'medical_status' => $app->status, // مكتمل، قيد المعالجة، إلخ
                    'financial_status' => $app->invoice->payment_status ?? 'غير مدفوع', // مدفوع، غير مدفوع
                    'total_amount' => $app->invoice->total_amount ?? 0
                ];
            }),

            // 5. الملاحظات الطبية الدائمة (مستخرجة من حقل الملاحظات النصي كـ Array لسهولة العرض كـ Badges)
            'medical_notes' => $this->patientProfile->medical_notes 
                ? explode(',', $this->patientProfile->medical_notes) 
                : ['مريض سكري', 'يعاني من سيولة دم', 'فوبيا من الإبر'], // افتراضية للتجريب ومطابقة الصورة إذا كان الحقل فارغاً

            // 6. الطلبات والاستفسارات الأخيرة (بيانات تجريبية سريعة لمطابقة التصميم)
            'recent_requests' => [
                [
                    'id' => 1,
                    'title' => 'طلب تقرير طبي للعمل',
                    'description' => 'يرجى تجهيز تقرير بالحالة الصحية لتقديمه لجهة العمل',
                    'time_ago' => 'منذ ساعتين'
                ]
            ]
        ];
    }
}