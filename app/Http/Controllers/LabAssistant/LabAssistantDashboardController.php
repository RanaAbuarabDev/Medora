<?php


namespace App\Http\Controllers\LabAssistant;

use App\Http\Controllers\Controller;
use App\Services\LabAssistant\LabAssistantDashboardService;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request; 

class LabAssistantDashboardController extends Controller
{
    protected $dashboardService;
    protected $apiResponse;

    public function __construct(LabAssistantDashboardService $dashboardService, ApiResponseService $apiResponse)
    {
        $this->dashboardService = $dashboardService;
        $this->apiResponse = $apiResponse;
    }

    /**
     * 1. جلب بيانات لوحة التحكم (العدادات + عينات صالة الانتظار فقط)
     */
    public function index()
    {
        try {
            $data = $this->dashboardService->getDashboardData();

            return $this->apiResponse->success(
                $data,
                'تم جلب بيانات لوحة التحكم والمواعيد المنتظرة بنجاح.'
            );

        } catch (Exception $e) {
            return $this->apiResponse->error(
                'حدث خطأ أثناء معالجة بيانات اللوحة: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * 2. استقبال حدث النقر على زر "بدء التحليل" وتحديث الحالة لـ in_progress
     */
    public function start($appointmentId)
    {
        try {
            $this->dashboardService->startAnalysis($appointmentId);

            return $this->apiResponse->success(
                null,
                'تم نقل العينة إلى المختبر وبدء العمل عليها بنجاح.'
            );

        } catch (Exception $e) {
            return $this->apiResponse->error(
                $e->getMessage(),
                400
            );
        }
    }


    

    /**
     * 3. جلب قائمة مواعيد اليوم العامة مع الفلترة الديناميكية
     * GET /api/lab-assistant/today-appointments?status=waiting
     */
    public function todayAppointments(Request $request)
    {
        try {
            // استقبال الفلتر من الرابط إن وُجد (الكل، قيد الانتظار، قيد التحليل)
            $statusFilter = $request->query('status');

            $data = $this->dashboardService->getTodayAppointments($statusFilter);

            return $this->apiResponse->success(
                $data,
                'تم جلب مواعيد اليوم بنجاح والتصنيفات متطابقة.'
            );

        } catch (Exception $e) {
            return $this->apiResponse->error(
                'حدث خطأ أثناء جلب مواعيد اليوم: ' . $e->getMessage(),
                500
            );
        }
    }
}