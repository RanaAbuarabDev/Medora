<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\GetAppointmentsRequest;
use App\Services\LabManager\LabOperationService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;
use Carbon\Carbon;

class OperationController extends Controller
{
    public function __construct(
        protected LabOperationService $operationService
    ) {}

    public function index(GetAppointmentsRequest $request): JsonResponse
    {
        try {
            $labId = Auth::user()->lab_id;

            if (!$labId) {
                return ApiResponseService::error(null, 'غير مصرح لك بالوصول لبيانات هذا المختبر', 403);
            }

            $result = $this->operationService->getOperationsData($labId, $request->validated());
            $paginator = $result['appointments_paginator'];
            
            // هندسة وتنسيق المخرجات لتطابق التصميم بالمليمتر
            $formattedItems = collect($paginator->items())->map(function ($appointment) {
                // جلب أسماء التحاليل المدمجة للموعد (مثل: CBC, فحص السكر التراكمي)
                $testsNames = $appointment->labTests->map(function($labTest) {
                    return $labTest->masterTest->name ?? ($labTest->name ?? 'تحليل طبي');
                })->implode(' - ');

                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->name ?? 'مريض كاش',
                    'test_type' => $testsNames ?: 'لم يحدد بعد',
                    'assigned_lab_assistant' => 'أحمد كمال', // يمكنكِ استبدالها لاحقاً بعلاقة الموظف الفعلي
                    'appointment_date' => Carbon::parse($appointment->appointment_date)->format('d أكتوبر Y'),
                    'appointment_time' => Carbon::parse($appointment->start_time)->format('h:i A'),
                    'status' => $appointment->status,
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'message' => trans('تم جلب جدول العمليات بنجاح'),
                'cards' => $result['cards'], 
                'data' => $formattedItems, // البيانات المنسقة العالية النقاء
                'pagination' => [
                    'total' => $paginator->total(),
                    'count' => $paginator->count(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages' => $paginator->lastPage()
                ]
            ], 200);

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء معالجة بيانات المواعيد والعمليات', 500);
        }
    }
}