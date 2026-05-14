<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function getStatistics()
    {
        // استخدام Query واحدة لجلب أغلب الأرقام لتقليل الضغط على السيرفر
        $totalLabs = Laboratory::count();
        $activeLabs = Laboratory::where('status', 'active')->count();
        
        // إحصائيات خاصة بواجهة المختبرات
        $joinedThisMonth = Laboratory::whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->count();
        
        // إحصائيات داشبورد الأدمن الأساسي
        $patientsCount = User::role('patient')->count();
        $totalRevenue = Subscription::where('status', 'paid')->sum('amount');
        $newUsersThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_labs' => $totalLabs,
                'active_labs' => $activeLabs,
                // حساب النسبة المئوية كما في الصورة
                'active_labs_percentage' => $totalLabs > 0 ? round(($activeLabs / $totalLabs) * 100) : 0, 
                'joined_this_month' => $joinedThisMonth,
                'total_patients' => $patientsCount,
                'revenue' => $totalRevenue,
                'new_users_week' => $newUsersThisWeek,
            ]
        ]);
    }

    public function getLabsList(Request $request)
    {
        // استخدام with('subscriptions') هو الـ Eager Loading لمنع مشكلة N+1
        // نفترض هنا أن العلاقة اسمها subscriptions في مودل Laboratory
        $query = Laboratory::with(['subscriptions' => function($q) {
            $q->where('status', 'paid');
        }]);

        // 1. البحث الذكي (Search Logic)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%')
                  ->orWhere('license_number', 'like', '%' . $request->search . '%');
            });
        }

        // 2. الفلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // جلب البيانات مع الترتيب والتقسيم لصفحات
        $labs = $query->latest()->paginate(10);

        // 3. تنسيق البيانات (Data Transformation)
        $formattedLabs = $labs->getCollection()->map(function ($lab) {
            return [
                'id' => $lab->id,
                'name' => $lab->name,
                'license_number' => $lab->license_number ?? 'N/A', //
                'location' => $lab->address ?? 'غير محدد',
                // تنسيق التاريخ المطلوب لتطابق الجداول
                'registration_date' => $lab->created_at->format('Y-m-d'), 
                'status' => $lab->status,
                // حساب الإيرادات من العلاقة المحملة مسبقاً (Eager Loaded) لضمان السرعة
                'revenue' => $lab->subscriptions->sum('amount'), 
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedLabs,
            'pagination' => [
                'total' => $labs->total(),
                'current_page' => $labs->currentPage(),
                'last_page' => $labs->lastPage(),
            ]
        ]);
    }
}