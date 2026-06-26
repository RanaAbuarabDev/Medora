<?php

namespace App\Services\Reception;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Laboratory;
use App\Models\LabSchedule;
use App\Models\PatientProfile;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ReceptionistDashboardService
{
    /**
     * جلب كافة بيانات لوحة تحكم موظف الاستقبال لليوم الحالي
     */
    public function getDashboardData(int $labId)
    {
        $today = Carbon::today()->format('Y-m-d');

        // 1. حساب الكروت العلوية (KPIs)
        $cards = [
            'waiting_list_count' => Appointment::where('lab_id', $labId)
                                        ->whereDate('appointment_date', $today)
                                        ->where('status', 'confirmed') 
                                        ->count(),
                                        
            'upcoming_appointments' => Appointment::where('lab_id', $labId)
                                        ->whereDate('appointment_date', $today)
                                        ->where('status', 'pending')
                                        ->count(),
                                        
            'pending_invoices_count' => Invoice::whereHas('appointment', function($q) use ($labId, $today) {
                                            $q->where('lab_id', $labId)->whereDate('appointment_date', $today);
                                        })->where('payment_status', 'unpaid')->count(),
                                        
            'completed_results_count' => Appointment::where('lab_id', $labId)
                                        ->whereDate('appointment_date', $today)
                                        ->where('status', 'completed')
                                        ->count(),
        ];

        // 2. جلب المواعيد القادمة لليوم مع العلاقات الصحيحة هندسياً ⚡
        $appointments = Appointment::where('lab_id', $labId)
            ->whereDate('appointment_date', $today)
            ->with([
                'patient:id,name', 
                'labTests.masterTest', // 👈 التعديل الجوهري هنا: شحن الـ masterTest بدلاً من طلب حقل name غير الموجود
                'invoice:id,appointment_id,total_amount,payment_status'
            ])
            ->orderBy('start_time', 'asc') 
            ->get();

        // 3. جلب التنبيهات العاجلة الحية
        $urgentAlerts = DB::table('notifications')
            ->whereNull('read_at')
            ->latest()
            ->take(3)
            ->get()
            ->map(function($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id'      => $notification->id,
                    'type'    => $data['type'] ?? 'general_alert',
                    'message' => $data['message'] ?? $notification->title ?? 'تنبيه نظام جديد'
                ];
            })->toArray();

        if (empty($urgentAlerts)) {
            $urgentAlerts = [
                ['type' => 'info', 'message' => 'لا توجد تنبيهات عاجلة حالياً لليوم.']
            ];
        }

        // 4. أحدث طلبات الاستفسار الحية 
        $recentInquiries = SupportTicket::where('laboratory_id', $labId)
            ->where('status', 'open')
            ->with('patient:id,name') 
            ->latest()
            ->take(2)
            ->get()
            ->map(function($ticket) {
                return [
                    'id'               => $ticket->id,
                    'patient_name'     => $ticket->patient->name ?? 'مريض خارجي',
                    'message'          => $ticket->message,
                    'created_at_human' => $ticket->created_at->diffForHumans()
                ];
            });

        return [
            'cards'             => $cards,
            'alerts'            => $urgentAlerts,
            'appointments'      => $appointments,
            'recent_inquiries'  => $recentInquiries
        ];
    }

    /**
     * التبديل السريع لحالة الدفع (Toggle Payment)
     */
    public function togglePaymentStatus(int $invoiceId): Invoice
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->payment_status = $invoice->payment_status === 'paid' ? 'unpaid' : 'paid';
        $invoice->amount_paid = $invoice->payment_status === 'paid' ? $invoice->total_amount : 0;
        $invoice->save();

        return $invoice;
    }

    public function searchPatients(string $query)
    {
        return User::role('patient')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->select('id', 'name', 'phone', 'email') 
            ->take(10) 
            ->get();
    }

    /**
     * حجز موعد جديد لمريض
     */
    public function storeAppointment(array $data, int $labId)
    {
        return DB::transaction(function () use ($data, $labId) {
            
            $patientId = $data['patient_id'] ?? null;

            if (!$patientId) {
                $user = User::create([
                    'name'     => $data['patient_name'],
                    'email'    => $data['email'] ?? 'patient_' . time() . '@medora.com',
                    'phone'    => $data['phone'],
                    'password' => Hash::make('password'),
                    'status'   => 'active',
                    'lab_id'   => $labId
                ]);
                $user->assignRole('patient');

                PatientProfile::create([
                    'user_id'          => $user->id,
                    'gender'           => $data['gender'],
                    'birth_date'       => $data['birth_date'],
                    'emergency_phone'  => $data['emergency_phone'] ?? null,
                    'address'          => $data['address'] ?? null,
                ]);

                $internalNumber = "LAB" . $labId . "-P-" . $user->id;
                $user->laboratories()->attach($labId, [
                    'internal_patient_number' => $internalNumber
                ]);

                $patientId = $user->id;
            }

            $appointmentCode = 'MED-' . rand(10000, 99999);

            $lab = Laboratory::find($labId);
            $interval = $lab->slot_interval ?? 15;

            $startTime = Carbon::parse($data['start_time']);
            $endTime = $startTime->copy()->addMinutes($interval)->format('H:i:s');

            $appointment = Appointment::create([
                'lab_id'           => $labId,
                'user_id'          => $patientId,
                'appointment_date' => $data['appointment_date'],
                'start_time'       => $data['start_time'],
                'end_time'         => $endTime,
                'status'           => 'confirmed', 
                'is_fasting'       => $data['is_fasting'] ?? false,
                'appointment_code' => $appointmentCode,
            ]);

            if (!empty($data['test_ids'])) {
                $appointment->labTests()->attach($data['test_ids']);
            }

            $paymentStatus = isset($data['confirm_cash']) && $data['confirm_cash'] ? 'paid' : 'unpaid';
            
            Invoice::create([
                'appointment_id' => $appointment->id,
                'total_amount'   => $data['total_amount'],
                'amount_paid'    => $paymentStatus === 'paid' ? $data['total_amount'] : 0,
                'payment_status' => $paymentStatus,
                'lab_id'         => $labId,
                'patient_id'     => $patientId,
            ]);

            return $appointment->load([
                'patient', 
                'invoice', 
                'labTests.masterTest' 
            ]);
        });
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

        $periods = \Carbon\CarbonPeriod::since($start)->minutes($interval)->until($end->copy()->subMinutes($interval));

        $appointmentsByTime = Appointment::where('lab_id', $labId)
                ->where('appointment_date', $date)
                ->where('status', Appointment::STATUS_CONFIRMED)
                ->select('start_time', DB::raw('count(*) as count'))
                ->groupBy('start_time')
                ->pluck('count', 'start_time')
                ->toArray();

        $slots = [];
        $now = Carbon::now();

        foreach ($periods as $period) {
            $time = $period->format('H:i:s');
            
            if ($date === $now->format('Y-m-d') && $period->lt($now)) {
                continue;
            }

            $bookedCount = $appointmentsByTime[$time] ?? 0;

            $slots[] = [
                'time' => $period->format('H:i'),
                'full_time' => $time,
                'is_available' => $bookedCount < $assistantsCount, 
                'remaining_slots' => max(0, $assistantsCount - $bookedCount)
            ];
        }

        return array_values($slots);
    }

    private function getLabAssistantsCount(int $labId): int
    {
        $count = User::where('lab_id', $labId)
            ->role('lab_assistant')
            ->count();

        return $count > 0 ? $count : 1;
    }

    public function getManageAppointments(int $labId, array $filters)
    {
        $query = Appointment::where('lab_id', $labId)
            ->with(['patient', 'invoice', 'labTests'])
            ->latest('appointment_date')
            ->latest('start_time');       

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pQ) use ($search) {
                    $pQ->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
                });

                $cleanSearch = str_replace('#INV-', '', $search);
                if (is_numeric($cleanSearch)) {
                    $q->orWhereHas('invoice', function ($iQ) use ($cleanSearch) {
                        $iQ->where('id', $cleanSearch);
                    });
                }
            });
        }

        if (!empty($filters['from_date'])) {
            $query->where('appointment_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('appointment_date', '<=', $filters['to_date']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function getPatientMedicalProfile(int $patientId)
    {
        return User::role('patient')
            ->with(['patientProfile', 'appointments.invoice', 'appointments.labTests'])
            ->findOrFail($patientId);
    }

    public function confirmPatientAttendance(int $appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->status !== 'pending') {
            throw new \Exception('لا يمكن تأكيد حضور مريض تم استقباله أو إلغاء موعده مسبقاً.');
        }

        $appointment->update([
            'status' => 'waiting'
        ]);

        return $appointment;
    }
}