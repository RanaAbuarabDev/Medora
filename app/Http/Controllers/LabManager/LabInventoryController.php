<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\InventoryStoreRequest;
use App\Http\Requests\LabManager\InventoryUpdateRequest;
use App\Services\LabManager\InventoryService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class LabInventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * GET /api/lab/inventory
     * عرض لوحة تحكم المخزن والجدول
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id; // استخراج المخبر المقفل من المستخدم المسجل حالياً
            $data = $this->inventoryService->getInventoryDashboard($labId, $request->all());
            
            return ApiResponseService::success($data, 'تم جلب بيانات المخزن والمستلزمات بنجاح');
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء تحميل المخزن', 500);
        }
    }

    /**
     * POST /api/lab/inventory
     * إضافة وربط مادة عامة جديدة بمخزن المختبر
     */
    public function store(InventoryStoreRequest $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $this->inventoryService->addItemToInventory($labId, $request->validated());
            
            return ApiResponseService::success([], 'تم إضافة المستلزم الطبي لمخزنك بنجاح', 201);
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل إضافة المادة للمخزن، قد تكون مضافة مسبقاً', 422);
        }
    }

    /**
     * PUT /api/lab/inventory/{id}
     * تحديث كميات مادة موجودة في مخزن المختبر
     */
    public function update(InventoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $this->inventoryService->updateInventoryItem($labId, $id, $request->validated());
            
            return ApiResponseService::success([], 'تم تحديث كميات المستلزم بنجاح');
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل تحديث بيانات المخزن المحددة', 500);
        }
    }

    /**
     * DELETE /api/lab/inventory/{id}
     * حذف مادة من مخزن المختبر
     */
    public function destroy($id): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $this->inventoryService->deleteInventoryItem($labId, $id);
            
            return ApiResponseService::success([], 'تم حذف المادة وإلغاء ارتباطها بمخزنك بنجاح');
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل حذف المادة المحددة من قاعدة البيانات', 500);
        }
    }
}