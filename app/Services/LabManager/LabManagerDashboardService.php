<?php

namespace App\Services\LabManager;

use App\Models\Appointment;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabManagerDashboardService
{
    public function getDashboardStats(int $labId): array
    {
        $today = Carbon::today();
        $currentYear = Carbon::now()->year;

        // 1. أداء خارق للكروت الطبية (استعلام واحد مجمع)
        $appointmentStats = Appointment::where('lab_id', $labId)
            ->selectRaw("
                COUNT(CASE WHEN DATE(appointment_date) = ? THEN 1 END) as today_count,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
            ", [$today->toDateString()])
            ->first();

        // 2. حساب الإيرادات اليومية كاش
        $dailyRevenue = Invoice::where('lab_id', $labId)
            ->where('payment_status', 'paid')
            ->whereDate('updated_at', $today)
            ->sum('amount_paid');

        // 3. المخطط الدائري: (Top 5 Lab Tests) مع ربط جدول الماستر الصحيح للاسم
        $topTests = DB::table('appointment_lab_test')
            ->join('lab_tests', 'appointment_lab_test.lab_test_id', '=', 'lab_tests.id')
            ->join('master_tests', 'lab_tests.master_test_id', '=', 'master_tests.id')
            ->join('appointments', 'appointment_lab_test.appointment_id', '=', 'appointments.id')
            ->where('appointments.lab_id', $labId)
            ->select('master_tests.name', DB::raw('COUNT(appointment_lab_test.lab_test_id) as total'))
            ->groupBy('master_tests.id', 'master_tests.name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 4. مخطط التحاليل الأسبوعي (من السبت للجمعة)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $endOfWeek   = Carbon::now()->endOfWeek(Carbon::FRIDAY);

        $rawWeeklyData = Appointment::where('lab_id', $labId)
            ->whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
            ->select(DB::raw('DAYOFWEEK(appointment_date) as day_num'), DB::raw('COUNT(*) as total'))
            ->groupBy('day_num')
            ->pluck('total', 'day_num')
            ->toArray();

        $weeklyMap = [7 => 'السبت', 1 => 'الأحد', 2 => 'الإثنين', 3 => 'الثلاثاء', 4 => 'الأربعاء', 5 => 'الخميس', 6 => 'الجمعة'];
        $weeklyChart = [];
        foreach ($weeklyMap as $dayNum => $dayName) {
            $weeklyChart[] = [
                'day'   => $dayName,
                'count' => $rawWeeklyData[$dayNum] ?? 0
            ];
        }

        // 5. مخطط نمو الإيرادات الشهرية السنوي (تم الحياكة البرمجية الصارمة هنا لتجنب الأخطاء السابقة)
        $rawMonthlyData = Invoice::where('lab_id', $labId)
            ->where('payment_status', 'paid')
            ->whereYear('updated_at', $currentYear)
            ->select(DB::raw('MONTH(updated_at) as month_num'), DB::raw('SUM(amount_paid) as total_revenue'))
            ->groupBy('month_num')
            ->pluck('total_revenue', 'month_num')
            ->toArray();

        $monthsArabic = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس', 
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        $monthlyChart = [];
        foreach ($monthsArabic as $monthNum => $monthName) {
            $monthlyChart[] = [
                'month'   => $monthName,
                'revenue' => (float) ($rawMonthlyData[$monthNum] ?? 0.0)
            ];
        }

        // 6. النشاط الأخير (Optimized Eager Loading)
        // 6. النشاط الأخير (تحديث الحقل ليصبح user_id بناءً على هيكلية جدول الـ appointments)
        $recentActivities = Appointment::where('lab_id', $labId)
            ->with([
                'patient' => function($query) {
                    $query->select('id', 'name'); // جلب فقط الـ id والاسم لتسريع الاستعلام
                }
            ])
            ->select('id', 'user_id', 'status', 'updated_at') // ⚡ تم التعديل هنا إلى user_id
            ->orderBy('updated_at', 'desc')
            ->take(4)
            ->get()
            ->map(function ($activity) {
                return [
                    'status' => $activity->status,
                    'patient_name' => $activity->patient->name ?? 'مريض كاش',
                    'updated_at_human' => $activity->updated_at->diffForHumans(),
                ];
            })->toArray();
            

        return [
            'cards' => [
                'today_appointments' => $appointmentStats->today_count ?? 0,
                'pending_results'    => $appointmentStats->pending_count ?? 0,
                'completed_results'  => $appointmentStats->completed_count ?? 0,
                'daily_revenue'      => (float) $dailyRevenue,
            ],
            'charts' => [
                'weekly_tests'    => $weeklyChart,
                'monthly_revenue' => $monthlyChart,
            ],
            'top_tests'         => $topTests,
            'recent_activities' => $recentActivities,
        ];
    }
}