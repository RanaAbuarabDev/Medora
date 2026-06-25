<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\ReportFilterRequest;
use App\Services\LabManager\LabReportService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Exception;

class ReportController extends Controller
{
    protected LabReportService $reportService;

    public function __construct(LabReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * جلب تحليلات وتقارير لوحة تحكم مدير المختبر
     * GET /api/lab/reports
     */
    public function index(ReportFilterRequest $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            $data = $this->reportService->getLabManagerReportData($labId, $request->validated());

            return ApiResponseService::success($data, 'تم توليد التقارير والتحليلات البيانية بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء إعداد التقارير', 500);
        }
    }
}