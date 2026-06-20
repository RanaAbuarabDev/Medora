<?php

namespace App\Repositories\LabManager;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
//use Illuminate\Support\Facades\DB;

class LabAppointmentRepository
{
    // 1. جلب كروت الإحصائيات الثلاثية بناءً على الـ Enum الحقيقي لقاعدة بياناتكِ
    public function getOperationCards(int $labId)
    {
        $today = Carbon::today()->toDateString();

        return Appointment::where('lab_id', $labId)
            ->selectRaw("
                COUNT(CASE WHEN appointment_date = ? THEN 1 END) as today_count,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as processing_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
            ", [$today])
            ->first();
    }

    // 2. جلب جدول العمليات المفلتر مع جلب نوع الفحص من الجدول الوسيط
    public function getFilteredAppointments(int $labId, array $filters, int $perPage = 5): LengthAwarePaginator
    {
        $query = Appointment::where('lab_id', $labId)
            ->with([
                'patient' => function ($q) {
                    $q->select('id', 'name');
                },
                // جلب التحاليل الطبية المرتبطة بهذا الموعد ليعرف الفرونت إند "نوع الفحص"
                'labTests.masterTest' => function ($q) {
                    $q->select('id', 'name');
                }
            ])
            ->select('id', 'user_id', 'appointment_date', 'start_time', 'status', 'updated_at')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc');

        // فلتر البحث باسم المريض
        if (!empty($filters['search'])) {
            $query->whereHas('patient', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        // فلتر الحالة (مع دمج الحالات الملغاة بجميع أنواعها إن طُلبت)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'cancelled') {
                $query->whereIn('status', ['cancelled_by_patient', 'cancelled_by_lab']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // الفلتر الزمني (اليوم / الأسبوع)
        if (!empty($filters['date_filter'])) {
            if ($filters['date_filter'] === 'today') {
                $query->whereDate('appointment_date', Carbon::today());
            } elseif ($filters['date_filter'] === 'weekly') {
                $query->whereBetween('appointment_date', [Carbon::now()->startOfWeek(Carbon::SATURDAY), Carbon::now()->endOfWeek(Carbon::FRIDAY)]);
            }
        }

        return $query->paginate($perPage);
    }
}