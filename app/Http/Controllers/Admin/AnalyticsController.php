<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AnalyticsService;
use App\Services\ApiResponseService; // المسار حسب الكود الذي أرسلتِه
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getAdminDashboardData();
            
            
            return ApiResponseService::success($data, 'تم جلب بيانات التقارير بنجاح', 200);
            
        } catch (\Exception $e) {
            
            return ApiResponseService::error(
                ['debug' => $e->getMessage()], 
                'حدث خطأ أثناء جلب التقارير',      
                500                            
            );
        }
    }
}