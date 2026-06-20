<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\IndexResultRequest;
use App\Services\LabManager\LabResultService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Exception;

class LabResultController extends Controller
{
    protected $resultService;

    // حقن السيرفس بداخل الكنترولر باحترافية
    public function __construct(LabResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    /**
     * عرض لوحة تحكم سجل النتائج والمواعيد
     */
    public function index(IndexResultRequest $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            if (!$labId) {
                return ApiResponseService::error(null, 'المستخدم غير مرتبط بمخبر معتمد', 403);
            }

            // 1. جلب الكروت الإحصائية العلوية من السيرفس
            $cards = $this->resultService->getTodayCardsStats($labId);

            // 2. جلب بيانات الجدول المفرزة والمقسمة صفحات من السيرفس
            $paginator = $this->resultService->getPaginatedResults($labId, $request->validated());

            // 3. عمل التنسيق (Mapping) للبيانات لتناسب خانات الفرونت إند
            $formattedResults = collect($paginator->items())->map(function ($row) {
                $createdAt = Carbon::parse($row->order_date);
                
                return [
                    'id'               => $row->pivot_id,
                    'patient_name'     => $row->patient_name,
                    'patient_avatar'   => mb_substr($row->patient_name, 0, 2), 
                    'test_name'        => $row->test_name . ' (' . ($row->test_short_name ?? 'فحص') . ')',
                    'date'             => $createdAt->format('Y/m/d'),
                    'time'             => $createdAt->format('h:i أ'),
                    'status'           => $row->appointment_status, 
                ];
            });

            // 4. تجميع مخرجات الجداول والباجينيشن بداخل مصفوفة واحدة متكاملة
            $responseData = [
                'cards' => $cards,
                'data'  => $formattedResults,
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages'  => $paginator->lastPage()
                ]
            ];

            // 5. استخدام الـ ApiResponseService الموحد بالمشروع لنجاح العملية
            return ApiResponseService::success($responseData, 'تم تحميل سجل نتائج ومواعيد المخبر بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ غير متوقع أثناء جلب سجل البيانات', 500);
        }
    }
}