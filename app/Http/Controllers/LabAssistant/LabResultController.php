<?php

namespace App\Http\Controllers\LabAssistant;

use App\Http\Controllers\Controller;
use App\Services\LabAssistant\LabResultService;
use App\Services\ApiResponseService;
use App\Http\Requests\LabAssistant\FilterLabResultsRequest; 
use Illuminate\Http\Request;
use Exception;

class LabResultController extends Controller
{
    protected $resultService;
    protected $apiResponse;

    public function __construct(LabResultService $resultService, ApiResponseService $apiResponse)
    {
        $this->resultService = $resultService;
        $this->apiResponse = $apiResponse;
    }

    /**
     * 1. البحث الذكي برقم العينة أو كود الحجز
     * GET /api/lab-assistant/results/search?sample_code=#LAB-1042
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'sample_code' => 'required|string'
            ]);

            $data = $this->resultService->searchBySampleCode($request->query('sample_code'));

            return $this->apiResponse->success($data, 'تم العثور على العينة وجلب التحاليل بنجاح.');

        } catch (Exception $e) {
            return $this->apiResponse->error($e->getMessage(), 400);
        }
    }

    /**
     * 2. تخزين نتائج التحاليل (مسودة أو إرسال نهائي)
     * POST /api/lab-assistant/appointments/{appointment}/results
     */
    public function store(Request $request, $appointmentId)
    {
        try {
            $request->validate([
                'action_type' => 'required|in:draft,complete', // التمييز بين المسودة والاعتماد الصارم
                'tests'       => 'required|array',
                'tests.*.appointment_test_id' => 'required|integer',
                'tests.*.result_value'        => 'nullable|string', // nullable للسماح بالمسودات الفارغة
            ]);

            $this->resultService->saveResults(
                $appointmentId,
                $request->input('tests'),
                $request->input('action_type')
            );

            $message = $request->input('action_type') === 'complete' 
                ? 'تم اعتماد النتائج الطبية وإرسال الملف بنجاح.' 
                : 'تم حفظ النتائج كمسودة مؤقتة بنجاح.';

            return $this->apiResponse->success(null, $message);

        } catch (Exception $e) {
            return $this->apiResponse->error($e->getMessage(), 500);
        }
    }


    


    public function index(FilterLabResultsRequest $request)
    {
        try {
            
            $data = $this->resultService->getPaginatedResults($request->validated());

            return $this->apiResponse->success(
                $data, 
                'تم جلب قائمة النتائج الطبية بنجاح .'
            );

        } catch (Exception $e) {
            return $this->apiResponse->error(
                'حدث خطأ أثناء جلب النتائج : ' . $e->getMessage(), 
                500
            );
        }
    }
}