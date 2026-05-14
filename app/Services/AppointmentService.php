<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Laboratory;
use App\Models\LabSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class AppointmentService
{
    
    public function storeAppointment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $lab = Laboratory::where('id', $data['lab_id'])->lockForUpdate()->firstOrFail();

            $this->validateLabAvailability($lab->id, $data['appointment_date'], $data['start_time']);

            $assistantsCount = $this->getLabAssistantsCount($lab->id);

            $existingAppointmentsCount = Appointment::where('lab_id', $data['lab_id'])
                ->where('appointment_date', $data['appointment_date'])
                ->where('start_time', $data['start_time'])
                ->where('status', 'confirmed') 
                ->lockForUpdate() 
                ->count();

            if ($existingAppointmentsCount >= $assistantsCount) {
                throw new Exception('عذراً، هذا الموعد تم حجزه بالكامل للتو من قبل مريض آخر.', 422);
            }

            return Appointment::create([
                'user_id'          => Auth::id(),
                'lab_id'           => $data['lab_id'],
                'master_test_id'   => $data['test_id'],
                'appointment_date' => $data['appointment_date'],
                'start_time'       => $data['start_time'],
                'end_time'         => Carbon::parse($data['start_time'])
                                        ->addMinutes($lab->slot_interval)
                                        ->format('H:i:s'),
                'status'           => 'confirmed', 
            ]);
        });
    }

    /**
     * التحقق من توافق الموعد مع جدول دوام المختبر
     */
    private function validateLabAvailability(int $labId, string $date, string $time)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek; 
        $requestedTime = Carbon::parse($time)->format('H:i:s');

        $schedule = LabSchedule::where('lab_id', $labId)
            ->where('day_of_week', $dayOfWeek)
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

    
     
    public function cancelAppointment(int $appointmentId, ?string $reason = null)
    {
        return DB::transaction(function () use ($appointmentId, $reason) {
            $appointment = Appointment::where('id', $appointmentId)->lockForUpdate()->firstOrFail();

            
            if ($appointment->status !== 'confirmed') {
                throw new Exception('لا يمكن إلغاء الموعد لأنه ملغى مسبقاً أو مكتمل.', 422);
            }

            if (Auth::id() === $appointment->user_id) {
                $appointment->status = 'cancelled_by_patient';
            } else {
                $appointment->status = 'cancelled_by_lab';
                $appointment->cancel_reason = $reason;
            }

            $appointment->save();
            return $appointment;
        });
    }

    private function getLabAssistantsCount(int $labId): int
    {
        $count = User::where('lab_id', $labId)
            ->role('lab_assistant')
            ->count();

        return $count > 0 ? $count : 1;
    }

    /**
     * جلب مواعيد المريض (تعديل الفلترة)
     */
    public function getPatientAppointments(int $patientId)
    {
        $appointments = Appointment::with(['lab', 'test'])
            ->where('user_id', $patientId)
            ->orderBy('appointment_date', 'desc')
            ->get();

        return [
            'upcoming' => $appointments->filter(function ($app) {
               
                return ($app->appointment_date >= now()->format('Y-m-d')) 
                    && $app->status === 'confirmed';
            })->values(),
            
            'past' => $appointments->filter(function ($app) {
             
                return ($app->appointment_date < now()->format('Y-m-d')) 
                    || in_array($app->status, ['cancelled_by_patient', 'cancelled_by_lab', 'completed']);
            })->values(),
        ];
    }

    public function getAppointmentDetails(int $id, int $patientId)
    {
        return Appointment::with(['lab', 'test'])
            ->where('user_id', $patientId)
            ->findOrFail($id);
    }


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
                ->where('status', 'confirmed')
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
}