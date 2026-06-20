<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getAdminDashboardData()
    {
        return [
            'statistics' => $this->getMainStats(),
            'charts' => [
                'growth_chart' => $this->getMonthlyGrowthData(),
            ],
            'top_lists' => [
                'best_labs' => $this->getTopPerformingLabs(),
                'most_requested_tests' => $this->getMostRequestedTests(),
            ]
        ];
    }

    private function getMainStats()
    {
        // حساب نسبة التحصيل المالي لهذا الشهر
        $currentMonth = now()->format('Y-m');
        $totalExpected = Subscription::where('billing_month', $currentMonth)->sum('amount');
        $totalCollected = Subscription::where('billing_month', $currentMonth)
            ->where('status', 'paid')
            ->sum('amount');

        return [
            'total_labs' => User::role('lab_manager')->count(), 
            'total_tests_executed' => DB::table('appointments')->count(), 
            'total_revenue' => number_format($totalCollected, 2) . ' $',
            'collection_rate' => $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) . '%' : '0%',
        ];
    }

    private function getMonthlyGrowthData()
    {
        // جلب نمو المواعيد (الفحوصات) لآخر 6 أشهر
        $data = DB::table('appointments')
            ->select(
                DB::raw("COUNT(*) as count"),
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("MONTH(created_at) as month_num")
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month', 'month_num')
            ->orderBy('month_num', 'ASC')
            ->get();

        return $data->map(fn($item) => [
            'label' => $item->month,
            'value' => $item->count
        ]);
    }

    private function getTopPerformingLabs()
    {
        // أكثر المخابر استقبالاً للمواعيد
        return DB::table('appointments')
            ->join('laboratories', 'appointments.lab_id', '=', 'laboratories.id')
            ->select('laboratories.name', DB::raw('COUNT(*) as tests_count'))
            ->groupBy('appointments.lab_id', 'laboratories.name')
            ->orderBy('tests_count', 'desc')
            ->take(5)
            ->get();
    }

    

    private function getMostRequestedTests()
    {
        
        return DB::table('appointment_lab_test')
            ->join('appointments', 'appointment_lab_test.appointment_id', '=', 'appointments.id')
            ->join('lab_tests', 'appointment_lab_test.lab_test_id', '=', 'lab_tests.id')
            ->join('master_tests', 'lab_tests.master_test_id', '=', 'master_tests.id') // الانتقال للتحليل الرئيسي لضم الاسم
            ->select('master_tests.name', DB::raw('COUNT(*) as total'))
            ->whereIn('appointments.status', ['confirmed', 'completed']) 
            ->groupBy('lab_tests.master_test_id', 'master_tests.name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
    }
}

