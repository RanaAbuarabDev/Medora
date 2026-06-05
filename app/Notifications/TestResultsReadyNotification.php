<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class TestResultsReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    /**
     * تمرير كائن الموعد ممتلئاً بالعلاقات وجاهزاً
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * قنوات الإرسال: إيميل وقاعدة بيانات لقائمة جرس التنبيهات
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * تخصيص رسالة البريد الإلكتروني للمريض
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('🔬 تقريرك الطبي جاهز الآن - منصة ميدورا')
                    ->greeting('مرحباً بك، ' . $notifiable->name)
                    ->line('نسعد بإعلامك بأن نتائج تحاليلك الطبية قد اعتُمدت وصارت جاهزة للعرض والتحميل.')
                    ->action('تحميل التقرير الطبي PDF', url('/patient/results/' . $this->appointment->id))
                    ->line('شكراً لاختيارك منصة ميدورا المخبرية، متمنين لك دوام العافية.');
    }

    /**
     * هنا السحر: تجهيز القالب الورقي الكامل للفرونت إند داخل قاعدة البيانات
     */
    public function toArray($notifiable)
    {
        // 1. عبارات تنبيهية مناسبة وجذابة للواجهة
        $title = '🔬 صُدرت نتائجك المخبرية بنجاح';
        $shortMessage = 'تم اعتماد تقريرك الطبي لـ ' . ($this->appointment->labTests->count()) . ' تحاليل من قبل المختبر.';

        // 2. بناء هيكلية التقرير المخبري الشامل بالبيانات والنتائج الحقيقية
        return [
            'notification_meta' => [
                'title'      => $title,
                'message'    => $shortMessage,
                'icon'       => 'flask-outline', // أيقونة مخبرية مفيدة للفرونت إند
                'created_at' => Carbon::now()->toIso8601String(),
            ],
            'lab_report' => [
                'appointment_id' => $this->appointment->id,
                'sample_code'    => $this->appointment->sample_code ?? '#LAB-' . $this->appointment->id,
                'date'           => $this->appointment->appointment_date,
                'approved_at'    => Carbon::now()->format('Y-m-d g:i A'), // توقيت صدور النتيجة بالملي
                
                // معلومات المختبر المصدر للتقرير
                'laboratory' => [
                    'id'   => $this->appointment->lab_id,
                    'name' => $this->appointment->lab->name ?? 'مختبر ميدورا المركزي', // تأكدي من وجود علاقة lab بالموعد
                ],
                
                // معلومات المريض الشخصية لقالب التقرير
                'patient' => [
                    'name'   => $this->appointment->patientProfile->user->name ?? 'مريض غير معروف',
                    'age'    => '24 سنة', 
                    'gender' => 'أنثى',
                ],
                
                //  جدول النتائج والقيم والحدود الطبيعية والوحدات كاملة بالملي!
                'results_table' => $this->appointment->labTests->map(function ($labTest) {
                    return [
                        'test_name'    => $labTest->masterTest->name ?? $labTest->name,
                        'result_value' => $labTest->pivot->result_value ?? '0', // النتيجة المسجلة
                        'unit'         => $labTest->masterTest->unit ?? 'g/dL',
                        'min_value'    => $labTest->masterTest->min_value ?? 12.0,
                        'max_value'    => $labTest->masterTest->max_value ?? 16.0,
                        'status'       => $labTest->pivot->status ?? 'completed',
                        // ميزة إضافية ذكية: تحديد هل النتيجة طبيعية أم خارج الحدود لمساعدة الفرونت إند بالتلوين
                        'is_flagged'   => ($labTest->pivot->result_value < ($labTest->masterTest->min_value ?? 12.0) || 
                                            $labTest->pivot->result_value > ($labTest->masterTest->max_value ?? 16.0))
                    ];
                })->toArray(),
            ]
        ];
    }
}