<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Services\LabManager\LabManagerDashboardService;
use App\Services\ApiResponseService; // الخدمة الموحدة الخاصة بكِ للإرجاع
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $apiResponse;

    // حقن الخدمات بداخل الباني
    public function __construct(
        LabManagerDashboardService $dashboardService,
        ApiResponseService $apiResponse
    ) {
        $this->dashboardService = $dashboardService;
        $this->apiResponse = $apiResponse;
    }

    public function index(): JsonResponse
    {
        try {
            // جلب الـ lab_id من الـ User الموثق حالياً
            $labId = Auth::user()->lab_id;

            if (!$labId) {
                // الترتيب: البيانات (null)، الرسالة، ثم الـ Status Code
                return ApiResponseService::error(null, 'غير مصرح لك بالوصول لبيانات هذا المختبر', 403);
            }

            // جلب البيانات من الـ Service المحسنة
            $data = $this->dashboardService->getDashboardStats($labId);

            // الترتيب الصحيح بحسب الـ Service الخاصة بكِ: البيانات أولاً، ثم الرسالة النصية
            return ApiResponseService::success($data, 'تم جلب بيانات لوحة التحكم بنجاح', 200);

        } catch (Exception $e) {
            // عند حدوث استثناء، نمرر تفاصيل الخطأ بداخل مصفوفة البيانات كما كنتِ تفعلين
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ داخلي في الخادم المالي والطبي', 500);
        }
    }
}