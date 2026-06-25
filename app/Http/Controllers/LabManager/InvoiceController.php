<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\InvoiceFilterRequest;
use App\Services\LabManager\InvoiceService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Exception;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * عرض لوحة الفواتير والمدفوعات لمدير المختبر
     * GET /api/lab/invoices
     */
    public function index(InvoiceFilterRequest $request): JsonResponse
    {
        try {
            // جلب الـ lab_id الخاص بمدير المختبر المسجل حالياً
            $labId = auth()->user()->lab_id;

            // جلب البيانات معالجة ومفلترة بالكامل من السيرفس
            $dashboardData = $this->invoiceService->getInvoicesDashboard($labId, $request->validated());

            return ApiResponseService::success($dashboardData, 'تم جلب سجلات الفواتير والمدفوعات بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء معالجة البيانات المالية', 500);
        }
    }
}