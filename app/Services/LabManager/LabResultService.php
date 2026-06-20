<?php

namespace App\Services\LabManager;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabResultService
{
    /**
     * جلب إحصائيات كروت المواعيد لليوم الحالي
     */
    public function getTodayCardsStats(int $labId): array
    {
        $today = Carbon::today();

        // 1. إجمالي مواعيد اليوم (الكل) -> يطابق الـ 26 في التصميم
        $totalToday = DB::table('appointments')
            ->where('lab_id', $labId)
            ->whereDate('created_at', $today)
            ->count();

        // 2. عدد المواعيد المكتملة لليوم فقط -> يطابق الـ 18 في التصميم
        $completedToday = DB::table('appointments')
            ->where('lab_id', $labId)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        // 3. عدد المواعيد قيد التنفيذ لليوم فقط -> يطابق الـ 6 في التصميم
        $inProgressToday = DB::table('appointments')
            ->where('lab_id', $labId)
            ->where('status', 'in_progress')
            ->whereDate('created_at', $today)
            ->count();

        return [
            'total_appointments_today'       => $totalToday,
            'completed_appointments_today'   => $completedToday,
            'in_progress_appointments_today' => $inProgressToday,
        ];
    }

    /**
     * جلب وتقسيم سجل المواعيد والتحاليل بناءً على الفلاتر
     */
    public function getPaginatedResults(int $labId, array $filters)
    {
        $query = DB::table('appointment_lab_test')
            ->join('appointments', 'appointment_lab_test.appointment_id', '=', 'appointments.id')
            ->join('users', 'appointments.user_id', '=', 'users.id') // ⚡ تم التعديل هنا إلى user_id بدلاً من patient_id
            ->join('lab_tests', 'appointment_lab_test.lab_test_id', '=', 'lab_tests.id')
            ->join('master_tests', 'lab_tests.master_test_id', '=', 'master_tests.id')
            ->where('appointments.lab_id', $labId)
            ->select(
                'appointment_lab_test.id as pivot_id',
                'users.name as patient_name',
                'master_tests.name as test_name',
                'master_tests.short_name as test_short_name',
                'appointments.created_at as order_date',
                'appointments.status as appointment_status'
            );

        // تطبيق فلتر البحث باسم المريض أو الفحص
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('users.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('master_tests.name', 'like', '%' . $filters['search'] . '%');
            });
        }

        // تطبيق فلتر الحالة
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('appointments.status', $filters['status']);
        }

        // تطبيق الفلترة الزمنية لليوم
        if (!empty($filters['date_filter']) && $filters['date_filter'] === 'today') {
            $query->whereDate('appointments.created_at', Carbon::today());
        }

        return $query->orderBy('appointments.created_at', 'desc')->paginate(5);
    }
}