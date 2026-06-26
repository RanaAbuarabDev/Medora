<?php

namespace App\Http\Controllers\LabAssistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabAssistant\ConsumeSuppliesRequest;
use App\Services\LabAssistant\LabInventoryUsageService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Exception;

class LabInventoryUsageController extends Controller
{
    protected $usageService;

    public function __construct(LabInventoryUsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * GET /api/assistant/inventory-cards
     * لجلب الكروت وعرضها بقيمة 0 الافتراضية
     */
    public function getCards(): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id; // تقييد البيانات بمختبر المساعد الحالي
            $cards = $this->usageService->getLabConsumablesCards($labId);
            
            return ApiResponseService::success($cards, 'تم جلب كروت المستلزمات بنجاح');
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل تحميل كروت المستلزمات', 500);
        }
    }

    /**
     * POST /api/assistant/appointments/{appointmentId}/consume
     * استقبال وحفظ كميات الاستهلاك الفعلي
     */
    public function submitUsage(ConsumeSuppliesRequest $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $this->usageService->consumeSupplies($labId, $request->validated()['consumables']);
            
            return ApiResponseService::success([], 'تم إرسال الاستهلاك وتحديث كميات المخزن بنجاح');
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل معالجة المستلزمات المستهلكة', 500);
        }
    }
}