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
use Request;

class ReceptionistDashboardService
{
    /**
     * جلب كافة بيانات لوحة تحكم موظف الاستقبال لليوم الحالي
     */
    public function getDashboardData(int $labId)
    {
        $today = Carbon::today()->format('Y-m-d');

        // 1. حساب الكروت العلوية (KPIs) لتطابق الأرقام الظاهرة في الـ UI تماماً
        $cards = [
            // قائمة الانتظار: المرضى المتواجدين حالياً في المختبر بانتظار سحب العينة
            'waiting_list_count' => Appointment::where('lab_id', $labId)
                                        ->whereDate('appointment_date', $today)
                                        ->where('status', 'confirmed') // أو الحالة الخاصة بـ "بانتظار السحب" عندكِ
                                        ->count(),
                                        
            // المواعيد المتبقية: المواعيد القادمة اليوم التي لم تبدأ أو ما زالت معلقة
            'upcoming_appointments' => Appointment::where('lab_id', $labId)
                                        ->whereDate('appointment_date', $today)
                                        ->where('status', 'pending')
                                        ->count(),
                                        
            // الفواتير المعلقة: الفواتير غير المدفوعة التابعة لمواعيد اليوم (تطابق كارت 3 فواتير معلقة)
            'pending_invoices_count' => Invoice::whereHas('appointment', function($q) use ($labId, $today) {
                                            $q->where('lab_id', $labId)->whereDate('appointment_date', $today);
                                        })->where('payment_status', 'unpaid')->count(),
                                        
            // نتائج جاهزة: التحاليل التي اكتملت نتائجها اليوم تماماً (تطابق كارت 8 نتائج جاهزة)
            'completed_results_count' => Appointment::where('lab_id', $labId)
                                        ->whereDate('appointment_date', $today)
                                        ->where('status', 'completed')
                                        ->count(),
        ];

        // 2. جلب المواعيد القادمة لليوم مع العلاقات (وتأكدي من شحن الـ invoice_id للـ Toggle Switch)
        $appointments = Appointment::where('lab_id', $labId)
            ->whereDate('appointment_date', $today)
            ->with([
                'patient:id,name', 
                'labTests:id,name', // إذا لم يكن هناك حقل code في جدول التحاليل نكتفي بالـ name
                'invoice:id,appointment_id,total_amount,payment_status'
            ])
            ->orderBy('start_time', 'asc') 
            ->get();

        // 3. جلب التنبيهات العاجلة الحية من جدول التنبيهات
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
     * التبديل السريع لحالة الدفع (Toggle Payment) من الجدول مباشرة
     */
    public function togglePaymentStatus(int $invoiceId): Invoice
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        $invoice->payment_status = $invoice->payment_status === 'paid' ? 'unpaid' : 'paid';
        
        // تحديث الكاش المدفوع فورا ليتطابق مع الـ محاسبة
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
            ->select('id', 'name', 'phone', 'email') // نرجع فقط البيانات التي تحتاجها واجهة السيرش للتوفير
            ->take(10) // نكتفي بأول 10 نتائج لتسريع الأداء
            ->get();
    }


    public function storeAppointment(array $data, int $labId)
    {
        return DB::transaction(function () use ($data, $labId) {
            
            $patientId = $data['patient_id'] ?? null;

            // 🛑 الحالة الأولى: مريض جديد كلياً
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

                // ✨ تحديث: إضافة حقول الطوارئ والعنوان المأخوذة من الواجهة لمطابقة المودال تماماً
                PatientProfile::create([
                    'user_id'          => $user->id,
                    'gender'           => $data['gender'],
                    'birth_date'       => $data['birth_date'],
                    'emergency_phone'  => $data['emergency_phone'] ?? null, // رقم الطوارئ (قريب)
                    'address'          => $data['address'] ?? null,         // عنوان المريض
                ]);

                $internalNumber = "LAB" . $labId . "-P-" . $user->id;
                $user->laboratories()->attach($labId, [
                    'internal_patient_number' => $internalNumber
                ]);

                $patientId = $user->id;
            }

            // 🗓️ توليد كود موعد فريد ومميز ليظهر في شاشة النجاح (مثال: MED-88241)
            $appointmentCode = 'MED-' . rand(10000, 99999);

            $appointment = Appointment::create([
                'lab_id'           => $labId,
                'patient_id'       => $patientId,
                'appointment_date' => $data['appointment_date'],
                'start_time'       => $data['start_time'],
                'status'           => 'confirmed', 
                'is_fasting'       => $data['is_fasting'] ?? false,
                'appointment_code' => $appointmentCode, // تأكدي من وجود هذا الحقل في جدول المواعيد بمشروعك
            ]);

            if (!empty($data['test_ids'])) {
                $appointment->labTests()->attach($data['test_ids']);
            }

            // 💰 توليد الفاتورة المالية الملحقة تلقائياً
            $paymentStatus = isset($data['confirm_cash']) && $data['confirm_cash'] ? 'paid' : 'unpaid';
            
            $invoice = Invoice::create([
                'appointment_id' => $appointment->id,
                'total_amount'   => $data['total_amount'],
                'amount_paid'    => $paymentStatus === 'paid' ? $data['total_amount'] : 0,
                'payment_status' => $paymentStatus,
            ]);

            // ✨ التعديل الهندسي الأهم لواجهة النجاح: 
            // شحن الموعد بالعلاقات ليعود للفرونت إند ممتلئاً بكل تفاصيل الفاتورة وأسماء التحاليل المختارة للطباعة فوراً
            return $appointment->load(['patient', 'invoice', 'labTests:id,name']);
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

        // ⚡ التعديل لمنع التعديل المباشر على كائن النهاية ولتوليد الفترات بنظافة
        $periods = \Carbon\CarbonPeriod::since($start)->minutes($interval)->until($end->copy()->subMinutes($interval));

        // جلب المواعيد المحجوزة لليوم
        $appointmentsByTime = Appointment::where('lab_id', $labId)
                ->where('appointment_date', $date)
                ->where('status', Appointment::STATUS_CONFIRMED) // تأكدي أن هذا هو الثابت الصحيح لمواعيد الانتظار
                ->select('start_time', DB::raw('count(*) as count'))
                ->groupBy('start_time')
                ->pluck('count', 'start_time')
                ->toArray();

        $slots = [];
        $now = Carbon::now(); // ⚡ الوقت الحالي للمقارنة من أجل حماية الماضي

        foreach ($periods as $period) {
            $time = $period->format('H:i:s');
            
            // 🔒 حل الـ Bug: إذا كان التاريخ هو اليوم، والوقت المولد قد مضى في الساعات الحقيقية، نتخطاه فوراً ولا نعرضه للواجهة
            if ($date === $now->format('Y-m-d') && $period->lt($now)) {
                continue;
            }

            $bookedCount = $appointmentsByTime[$time] ?? 0;

            $slots[] = [
                'time' => $period->format('H:i'),
                'full_time' => $time,
                'is_available' => $bookedCount < $assistantsCount, 
                'remaining_slots' => max(0, $assistantsCount - $bookedCount) // استخدام max لضمان عدم خروج أرقام سالبة تحت أي ظرف
            ];
        }

        return array_values($slots); // إعادة ترتيب المصفوفة بنظافة بعد الـ continue
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
                // البحث في بيانات المريض
                // لارافيل هنا تبحث بربط علاقة المريض، وسنضمن استخدام الاسم الصحيح للعمود المشترك
                $q->whereHas('patient', function ($pQ) use ($search) {
                    $pQ->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
                });

                // البحث برقم الفاتورة (id) فقط إذا كان النص المدخل يحتوي على أرقام
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

    /**
     * تأكيد حضور المريض وتغيير حالة الموعد إلى "في الانتظار"
     */
    public function confirmPatientAttendance(int $appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // التحقق من أن الحالة الحالية هي pending فقط لزيادة أمان النظام
        if ($appointment->status !== 'pending') {
            throw new \Exception('لا يمكن تأكيد حضور مريض تم استقباله أو إلغاء موعده مسبقاً.');
        }

        // تحديث الحالة
        $appointment->update([
            'status' => 'waiting'
        ]);

        return $appointment;
    }

}