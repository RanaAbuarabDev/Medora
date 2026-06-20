<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Laboratory;
use App\Models\LabSchedule;
use App\Models\Invoice;
use App\Models\User;
use App\Models\LabTest; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class AppointmentService
{
    /**
     * حجز موعد جديد للمريض (نسخة خبيرة محمية ضد التلاعب والتنافسية)
     */
    /**
     * حجز موعد جديد للمريض (النسخة النهائية المستقرة والمتوافقة مع الفرونت إند)
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function storeAppointment(array $data)
    {
        $this->ensureTestsAreSelected($data);

        return DB::transaction(function () use ($data) {
            
            // تحديد هوية المريض المسؤول عن الحجز
            $patientId = Auth::id() ?? $data['user_id'] ?? null;
            if (!$patientId) {
                throw new Exception('لم يتم التعرف على هوية المستخدم المسؤول عن الحجز.', 401);
            }

            // 1. قفل صف المختبر فوراً في قاعدة البيانات لمنع أي حجز متزامن يتلاعب بالطاقة الاستيعابية
            $lab = Laboratory::where('id', $data['lab_id'])->lockForUpdate()->firstOrFail();

            // 2. التحقق من أوقات الدوام وصلاحية الوقت وتجنب الساعات الماضية تماماً
            $this->validateLabAvailability($lab->id, $data['appointment_date'], $data['start_time']);

            // 3. حماية: منع المريض من حجز أكثر من فترة في نفس اليوم لنفس المختبر
            $this->validatePatientDailyLimit($patientId, $lab->id, $data['appointment_date']);

            // 4. التحقق من الطاقة الاستيعابية بناءً على عدد المساعدين الحقيقيين للمختبر
            $this->checkLabCapacity($lab->id, $data['appointment_date'], $data['start_time']);

            // 5. إنشاء الموعد الأساسي بالحالة الافتراضية الأولى
            $appointment = $this->createNewAppointment($lab, $data, $patientId);

            // 6. ربط مصفوفة التحاليل بجدول الـ Pivot دفعة واحدة بكفاءة عالية (Eager Sync)
            $syncData = [];
            $now = Carbon::now();
            foreach ($data['test_ids'] as $testId) {
                $syncData[$testId] = [
                    'result_value' => null,       
                    'status'       => 'pending',  
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }
            $appointment->labTests()->attach($syncData);

            // 7. توليد الفاتورة التلقائية "مرة واحدة فقط" بكافة حقول الربط المدعومة (lab_id & patient_id)
            $this->generateInvoiceForAppointment($appointment->id, $lab->id, $patientId, $data['test_ids']);

            // 8. جلب العلاقات المطلوبة دفعة واحدة لبناء الاستجابة النظيفة
            $appointment->load(['labTests', 'invoice', 'lab']);

            // 🌟 صياغة الـ Response المثالي والمختصر المتوافق مع شاشات الفلاتر
            return [
                'status'  => 'success',
                'message' => 'تم تسجيل حجز الموعد وتوليد الفاتورة بنجاح.',
                'data'    => [
                    'appointment_id'   => $appointment->id,
                    'sample_code'      => $appointment->sample_code, // الكود الأساسي للمريض في المختبر
                    'appointment_date' => $appointment->appointment_date,
                    'start_time'       => Carbon::parse($appointment->start_time)->format('H:i'),
                    'status'           => $appointment->status,
                    
                    // بيانات المختبر المختصرة
                    'laboratory' => [
                        'id'   => $appointment->lab->id,
                        'name' => $appointment->lab->name,
                    ],
                    
                    // بيانات الفاتورة المفلترة بدون حشو
                    'invoice' => [
                        'id'             => $appointment->invoice->id,
                        'total_amount'   => (float) $appointment->invoice->total_amount, // تحويل قسري لـ Float
                        'payment_status' => $appointment->invoice->payment_status,
                    ],
                    
                    // قائمة التحاليل المطلوبة لمراجعتها فوراً بالواجهة
                    'tests' => $appointment->labTests->map(function ($test) {
                        return [
                            'id'    => $test->id,
                            'name'  => $test->name, 
                            'price' => (float) $test->pivot->price,
                        ];
                    }),
                ]
            ];
        });
    }

    /**
     * منع المريض من تكرار حجز الفترات بنفس اليوم
     */
    private function validatePatientDailyLimit(int $patientId, int $labId, string $date): void
    {
        $hasExistingAppointment = Appointment::where('user_id', $patientId)
            ->where('lab_id', $labId)
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'waiting', 'in_progress', Appointment::STATUS_CONFIRMED])
            ->exists();

        if ($hasExistingAppointment) {
            throw new Exception('عذراً، لا يمكنك حجز أكثر من موعد في نفس اليوم بداخل هذا المختبر الطبي.', 422);
        }
    }

    /**
     * التحقق من أوقات دوام المختبر وصلاحية الوقت المختار تاريخياً وعملياً
     */
    private function validateLabAvailability(int $labId, string $date, string $time): void
    {
        $requestedDateTime = Carbon::parse($date . ' ' . $time);
        $now = Carbon::now(); 

        // حل بغ الوقت الماضي: إذا كان التاريخ هو اليوم، نمنع حجز أي ساعة مرت وانتهت
        if ($requestedDateTime->isToday() && $requestedDateTime->lt($now)) {
            throw new Exception('عذراً، لا يمكن حجز وقت مضى من اليوم. يرجى اختيار فترة قادمة.', 422);
        }

        // منع الحجز في تاريخ قديم تماماً
        if ($requestedDateTime->isPast() && !$requestedDateTime->isToday()) {
            throw new Exception('لا يمكن حجز موعد في تاريخ قديم.', 422);
        }

        $schedule = LabSchedule::where('lab_id', $labId)
            ->where('day_of_week', $requestedDateTime->dayOfWeek)
            ->first();

        if (!$schedule) {
            throw new Exception('جدول دوام المختبر غير متوفر لهذا اليوم.', 422);
        }

        if ($schedule->is_day_off) {
            throw new Exception('المختبر مغلق في هذا اليوم، يرجى اختيار تاريخ آخر.', 422);
        }

        $requestedTime = Carbon::parse($time)->format('H:i:s');
        if ($requestedTime < $schedule->start_time || $requestedTime >= $schedule->end_time) {
            throw new Exception("المختبر يعمل من {$schedule->start_time} حتى {$schedule->end_time}.", 422);
        }
    }

    /**
     * التحقق من الطاقة الاستيعابية للفترة الزمنية المحددة داخل المختبر
     */
    private function checkLabCapacity(int $labId, string $date, string $time): void
    {
        $assistantsCount = $this->getLabAssistantsCount($labId);

        $existingAppointmentsCount = Appointment::where('lab_id', $labId)
            ->where('appointment_date', $date)
            ->where('start_time', $time)
            ->whereIn('status', ['pending', 'waiting', 'in_progress', Appointment::STATUS_CONFIRMED]) 
            ->count();

        if ($existingAppointmentsCount >= $assistantsCount) {
            throw new Exception('عذراً، هذا الموعد تم حجزه بالكامل للتو من قبل مريض آخر.', 422);
        }
    }

    /**
     * جلب مواعيد المريض (تصنيف ذكي ومحسّن الأداء)
     */
    public function getPatientAppointments(int $patientId)
    {
        $appointments = Appointment::with(['lab', 'labTests', 'invoice'])
            ->where('user_id', $patientId)
            ->orderBy('appointment_date', 'desc')
            ->get();

        $today = Carbon::today()->toDateString();

        return [
            'upcoming' => $appointments->filter(function ($app) use ($today) {
                return ($app->appointment_date >= $today) 
                    && in_array($app->status, ['pending', 'waiting', 'in_progress', Appointment::STATUS_CONFIRMED]);
            })->values(),
            
            'past' => $appointments->filter(function ($app) use ($today) {
                return ($app->appointment_date < $today) 
                    || in_array($app->status, [
                        Appointment::STATUS_CANCELLED_BY_PATIENT, 
                        Appointment::STATUS_CANCELLED_BY_LAB, 
                        'completed'
                    ]);
            })->values(),
        ];
    }

    /**
     * إلغاء الموعد
     */
    public function cancelAppointment(int $appointmentId, ?string $reason = null)
    {
        return DB::transaction(function () use ($appointmentId, $reason) {
            $appointment = Appointment::where('id', $appointmentId)->lockForUpdate()->firstOrFail();

            if (in_array($appointment->status, ['completed', Appointment::STATUS_CANCELLED_BY_PATIENT, Appointment::STATUS_CANCELLED_BY_LAB])) {
                throw new Exception('لا يمكن إلغاء الموعد لأنه ملغى مسبقاً أو مكتمل المعالجة.', 422);
            }

            $appointment->status = (Auth::id() === $appointment->user_id)
                ? Appointment::STATUS_CANCELLED_BY_PATIENT
                : Appointment::STATUS_CANCELLED_BY_LAB;

            if ($reason && $appointment->status === Appointment::STATUS_CANCELLED_BY_LAB) {
                $appointment->cancel_reason = $reason;
            }

            $appointment->save();
            return $appointment;
        });
    }

    /**
     * جلب تفاصيل موعد محدد للمريض
     */
    public function getAppointmentDetails(int $id, int $patientId)
    {
        return Appointment::with(['lab', 'labTests', 'invoice'])
            ->where('user_id', $patientId)
            ->findOrFail($id);
    }

    /**
     * حساب الفترات الزمنية المتاحة للحجز بناءً على طاقة المختبر الاستيعابية والوقت الحالي
     */
    public function getAvailableSlots(int $labId, string $date)
    {
        $lab = Laboratory::findOrFail($labId);
        $assistantsCount = $this->getLabAssistantsCount($labId);
        
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $schedule = LabSchedule::where('lab_id', $labId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$schedule || $schedule->is_day_off) {
            return []; 
        }

        $start = Carbon::parse($date . ' ' . $schedule->start_time);
        $end = Carbon::parse($date . ' ' . $schedule->end_time);
        $interval = $lab->slot_interval ?? 30; 

        $periods = \Carbon\CarbonPeriod::since($start)->minutes($interval)->until($end->subMinutes($interval));

        $appointmentsByTime = Appointment::where('lab_id', $labId)
                ->where('appointment_date', $date)
                ->whereIn('status', ['pending', 'waiting', 'in_progress', Appointment::STATUS_CONFIRMED])
                ->select('start_time', DB::raw('count(*) as count'))
                ->groupBy('start_time')
                ->pluck('count', 'start_time')
                ->toArray();

        $slots = [];
        $now = Carbon::now();

        foreach ($periods as $period) {
            $time = $period->format('H:i:s');
            $bookedCount = $appointmentsByTime[$time] ?? 0;
            
            // جعل الفترات الماضية من اليوم الحالي غير متاحة
            $isTimePast = $period->isToday() && $period->lt($now);

            $slots[] = [
                'time' => $period->format('H:i'),
                'full_time' => $time,
                'is_available' => !$isTimePast && ($bookedCount < $assistantsCount), 
                'remaining_slots' => $isTimePast ? 0 : max(0, $assistantsCount - $bookedCount)
            ];
        }

        return $slots;
    }

    /*
    |--------------------------------------------------------------------------
    | الدوال المساعدة الخاصة (Private Helper Methods)
    |--------------------------------------------------------------------------
    */

    private function ensureTestsAreSelected(array $data): void
    {
        if (!isset($data['test_ids']) || !is_array($data['test_ids']) || empty($data['test_ids'])) {
            throw new Exception('يرجى اختيار تحليل واحد على الأقل لإتمام عملية الحجز.', 422);
        }
    }

    private function createNewAppointment(Laboratory $lab, array $data, int $patientId): Appointment
    {
        return Appointment::create([
            'user_id'          => $patientId, 
            'lab_id'           => $lab->id,
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => Carbon::parse($data['start_time'])
                                    ->addMinutes($lab->slot_interval ?? 30)
                                    ->format('H:i:s'),
            'status'           => 'pending', 
            'sample_code'      => 'LAB-' . strtoupper(bin2hex(random_bytes(3))), 
            'master_test_id'   => $data['test_ids'][0] ?? null, 
        ]);
    }

    private function generateInvoiceForAppointment(int $appointmentId, int $labId, int $patientId, array $testIds): void
    {
        $totalAmount = LabTest::whereIn('id', $testIds)->sum('price');

        Invoice::create([
            'appointment_id' => $appointmentId,
            'lab_id'         => $labId,
            'patient_id'     => $patientId, // 🔥 الحل هنا: تمرير معرف المريض للفاتورة
            'total_amount'   => $totalAmount,
            'amount_paid'    => 0, 
            'payment_status' => Invoice::STATUS_UNPAID, 
        ]);
    }

    private function getLabAssistantsCount(int $labId): int
    {
        $count = User::where('lab_id', $labId)
            ->role('lab_assistant')
            ->count();

        return $count > 0 ? $count : 1;
    }
}