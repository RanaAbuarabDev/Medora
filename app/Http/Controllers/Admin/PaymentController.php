<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Http\Resources\Admin\SubscriptionResource;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = now()->format('Y-m');

        // 1. الإحصائيات للكروت العلوية (Top Cards)
        $stats = [
            'total_expected' => (float) Subscription::where('billing_month', $currentMonth)->sum('amount'),
            'total_collected' => (float) Subscription::where('billing_month', $currentMonth)->where('status', 'paid')->sum('amount'),
            'pending_labs_count' => Subscription::where('billing_month', $currentMonth)->where('status', 'pending')->count(),
            'overdue_labs_count' => Subscription::where('status', 'overdue')->count(),
        ];

        // 2. بناء الاستعلام للجدول (Table Query)
        // نستخدم with('lab') لمنع مشكلة الـ N+1 وتسريع الاستعلام
        $query = Subscription::with('lab');

        // الفلترة حسب الحالة (الكل، مدفوع، معلق)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // البحث باسم المختبر
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);

            $query->whereHas('lab', function($q) use ($searchTerm) {
                $q->where(function($innerQuery) use ($searchTerm) {
                    // البحث في جدول المستخدمين (اسم صاحب المختبر وإيميله)
                    $innerQuery->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                            // إذا كان لديكِ حقل لاسم المختبر في جدول المستخدمين أو جدول ملحق
                            ->orWhere('lab_name', 'LIKE', "%{$searchTerm}%"); 
                });
            });
        }

        // جلب البيانات مع التقسيم لصفحات (Pagination)
        $subscriptions = $query->latest()->paginate(10);

        
        return response()->json([
            'status' => 'success',
            'statistics' => $stats,                                
            'chart_data' => $this->getSubscriptionChartData(),     
            'quick_insights' => $this->getQuickInsights(),         
            'data' => SubscriptionResource::collection($subscriptions), 
            'pagination' => [
                'total' => $subscriptions->total(),
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
            ]
        ]);
    }

    
    public function markAsPaid($id)
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->status === 'paid') {
            return response()->json(['message' => 'هذه الفاتورة مدفوعة مسبقاً'], 400);
        }

        $subscription->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تأكيد استلام المبلغ كاش وتحديث السجل المالي',
            'data' => new SubscriptionResource($subscription)
        ]);
    }


    /**
     * جلب بيانات المخطط البياني (آخر 6 أشهر)
     */
    private function getSubscriptionChartData()
    {
        $chartData = [];
        
        // توليد آخر 6 أشهر ديناميكياً
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            
            // المبلغ المفروض تحصيله (كل الفواتير لهذا الشهر)
            $expected = Subscription::where('billing_month', $month)->sum('amount');
            
            // المبلغ المحصل فعلياً (الفواتير المدفوعة فقط)
            $collected = Subscription::where('billing_month', $month)
                                    ->where('status', 'paid')
                                    ->sum('amount');

            $chartData[] = [
                'month' => now()->subMonths($i)->translatedFormat('F'), // اسم الشهر بالعربي
                'expected' => (float)$expected,
                'collected' => (float)$collected,
            ];
        }
        
        return $chartData;
    }

    /**
     * جلب لمحات سريعة (Quick Insights)
     */
    private function getQuickInsights()
    {
        $currentMonth = now()->format('Y-m');

        return [
            'committed_labs' => Subscription::where('billing_month', $currentMonth)
                                            ->where('status', 'paid')
                                            ->count(),
                                            
            'overdue_labs' => Subscription::where('billing_month', $currentMonth)
                                        ->where('status', 'pending')
                                        ->count(),
                                        
            'total_debt' => Subscription::where('status', '!=', 'paid')
                                        ->sum('amount') . ' $',
        ];
    }
    public function sendReminders()
    {
        // جلب المخابر التي حالتها pending لهذا الشهر فقط
        $overdueSubscriptions = Subscription::where('status', 'pending')
            ->where('billing_month', now()->format('Y-m'))
            ->with('lab')
            ->get();

        if ($overdueSubscriptions->isEmpty()) {
            return response()->json(['message' => 'لا يوجد مخابر متأخرة حالياً'], 404);
        }

        foreach ($overdueSubscriptions as $subscription) {
            // إرسال الإشعار لصاحب المخبر
            $subscription->lab->notify(new PaymentReminderNotification($subscription));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال ' . $overdueSubscriptions->count() . ' تذكير بنجاح'
        ]);
    }
}