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
     * حجز موعد جديد للمريض (نسخة نظيفة ومحسنة الأداء)
     */
    public function storeAppointment(array $data)
    {
        $this->ensureTestsAreSelected($data);

        return DB::transaction(function () use ($data) {
            $lab = Laboratory::where('id', $data['lab_id'])->lockForUpdate()->firstOrFail();

            $this->validateLabAvailability($lab->id, $data['appointment_date'], $data['start_time']);
            $this->checkLabCapacity($lab->id, $data['appointment_date'], $data['start_time']);

            // 1. إنشاء الموعد
            $appointment = $this->createNewAppointment($lab, $data);

            // 2. ربط التحاليل بالموعد
            $appointment->labTests()->attach($data['test_ids']);

            // 3. توليد الفاتورة التلقائية
            $this->generateInvoiceForAppointment($appointment->id, $data['test_ids']);

            return $appointment->load(['labTests', 'invoice']);
        });
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

        // تحسين الأداء: حساب التاريخ الحالي مرة واحدة وتمريره عبر استخدام use
        $today = Carbon::today()->toDateString();

        return [
            'upcoming' => $appointments->filter(function ($app) use ($today) {
                return ($app->appointment_date >= $today) 
                    && $app->status === Appointment::STATUS_CONFIRMED; // استخدام الـ Constant
            })->values(),
            
            'past' => $appointments->filter(function ($app) use ($today) {
                return ($app->appointment_date < $today) 
                    || in_array($app->status, [
                        Appointment::STATUS_CANCELLED_BY_PATIENT, 
                        Appointment::STATUS_CANCELLED_BY_LAB, 
                        Appointment::STATUS_COMPLETED
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

            if ($appointment->status !== Appointment::STATUS_CONFIRMED) {
                throw new Exception('لا يمكن إلغاء الموعد لأنه ملغى مسبقاً أو مكتمل.', 422);
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
     * حساب الفترات الزمنية المتاحة للحجز بناءً على طاقة المختبر الاستيعابية
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
                ->where('status', Appointment::STATUS_CONFIRMED)
                ->select('start_time', DB::raw('count(*) as count'))
                ->groupBy('start_time')
                ->pluck('count', 'start_time')
                ->toArray();

        $slots = [];
        foreach ($periods as $period) {
            $time = $period->format('H:i:s');
            $bookedCount = $appointmentsByTime[$time] ?? 0;

            $slots[] = [
                'time' => $period->format('H:i'),
                'full_time' => $time,
                'is_available' => $bookedCount < $assistantsCount, 
                'remaining_slots' => $assistantsCount - $bookedCount 
            ];
        }

        return $slots;
    }

    /*
    |--------------------------------------------------------------------------
    | الدوال المساعدة الخاصة (Private Helper Methods) - SRP Principle
    |--------------------------------------------------------------------------
    */

    private function ensureTestsAreSelected(array $data): void
    {
        if (!isset($data['test_ids']) || !is_array($data['test_ids']) || empty($data['test_ids'])) {
            throw new Exception('يرجى اختيار تحليل واحد على الألق لإتمام عملية الحجز.', 422);
        }
    }

    private function checkLabCapacity(int $labId, string $date, string $time): void
    {
        $assistantsCount = $this->getLabAssistantsCount($labId);

        $existingAppointmentsCount = Appointment::where('lab_id', $labId)
            ->where('appointment_date', $date)
            ->where('start_time', $time)
            ->where('status', Appointment::STATUS_CONFIRMED) 
            ->lockForUpdate() 
            ->count();

        if ($existingAppointmentsCount >= $assistantsCount) {
            throw new Exception('عذراً، هذا الموعد تم حجزه بالكامل للتو من قبل مريض آخر.', 422);
        }
    }

    private function createNewAppointment(Laboratory $lab, array $data): Appointment
    {
        return Appointment::create([
            'user_id'          => Auth::id(),
            'lab_id'           => $lab->id,
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => Carbon::parse($data['start_time'])
                                    ->addMinutes($lab->slot_interval)
                                    ->format('H:i:s'),
            'status'           => Appointment::STATUS_CONFIRMED, 
        ]);
    }

    private function generateInvoiceForAppointment(int $appointmentId, array $testIds): void
    {
        $totalAmount = LabTest::whereIn('id', $testIds)->sum('price');

        Invoice::create([
            'appointment_id' => $appointmentId,
            'total_amount'   => $totalAmount,
            'amount_paid'    => 0, 
            'payment_status' => Invoice::STATUS_UNPAID, // استخدام Constant الفاتورة
        ]);
    }

    private function validateLabAvailability(int $labId, string $date, string $time): void
    {
        $carbonDate = Carbon::parse($date);
        $requestedTime = Carbon::parse($time)->format('H:i:s');

        $schedule = LabSchedule::where('lab_id', $labId)
            ->where('day_of_week', $carbonDate->dayOfWeek)
            ->first();

        if (!$schedule) {
            throw new Exception('جدول دوام المختبر غير متوفر لهذا اليوم.', 422);
        }

        if ($schedule->is_day_off) {
            throw new Exception('المختبر مغلق في هذا اليوم، يرجى اختيار تاريخ آخر.', 422);
        }

        if ($requestedTime < $schedule->start_time || $requestedTime >= $schedule->end_time) {
            throw new Exception("المختبر يعمل من {$schedule->start_time} حتى {$schedule->end_time}.", 422);
        }
    }

    private function getLabAssistantsCount(int $labId): int
    {
        $count = User::where('lab_id', $labId)
            ->role('lab_assistant')
            ->count();

        return $count > 0 ? $count : 1;
    }
}