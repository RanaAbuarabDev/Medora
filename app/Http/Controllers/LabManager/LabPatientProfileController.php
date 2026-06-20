<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Services\LabManager\LabPatientProfileService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Exception;

class LabPatientProfileController extends Controller
{
    protected $profileService;

    public function __construct(LabPatientProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * عرض الملف الطبي التفصيلي للمريض المحدد داخل المخبر الحالي
     */
    public function show(int $id): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            if (!$labId) {
                return ApiResponseService::error(null, 'المستخدم غير مرتبط بمخبر معتمد', 403);
            }

            // 1. جلب تفاصيل المريض الشخصية (محصورة بالمخبر لضمان الأمان والخصوصية)
            $patient = $this->profileService->getPatientDetails($id, $labId);

            // إذا كان المريض غير موجود، أو لم يقم بأي زيارة للمخبر الحالي من قبل
            if (!$patient || !$patient->last_visit_date) {
                return ApiResponseService::error(null, 'الملف الطبي غير متاح أو لا توجد زيارات سابقة لهذا المريض في مخبركم', 404);
            }

            // 2. جلب سجل الفحوصات والتحاليل الخاصة به في هذا المخبر
            $testsHistory = $this->profileService->getPatientTestsHistory($id, $labId);

            // 3. تنسيق مصفوفة الفحوصات لتطابق الجدول (CBC, LFT...)
            $formattedTests = $testsHistory->map(function ($test) {
                $date = Carbon::parse($test->test_date);
                
                // تنسيق تاريخ الطلب والوقت بدقة لتطابق تصميم السجل المرفق
                return [
                    'id'               => $test->pivot_id,
                    'test_name'        => $test->test_name . ' (' . ($test->test_short_name ?? 'فحص') . ')',
                    'date'             => $date->translatedFormat('d أكتوبر Y'), // مثال: 15 أكتوبر 2023
                    'time'             => $date->format('h:i أ'), // مثال: 09:15 ص
                    'status'           => $test->test_status, // completed, in_progress
                    'pdf_url'          => $test->test_status === 'completed' ? url("/api/lab/results/pdf/{$test->pivot_id}") : null
                ];
            });

            // 4. تجميع الاستجابة النهائية المتطابقة مع الكروت الفوق والجانبية في التصميم
            $responseData = [
                'personal_info' => [
                    'id'            => '#PT-' . str_pad($patient->id, 4, '0', STR_PAD_LEFT), // كود المريض الاحترافي #PT-88291
                    'name'          => $patient->name,
                    'age'           => $patient->birth_date ? Carbon::parse($patient->birth_date)->age . ' عاماً' : 'غير محدد',
                    'gender'        => $patient->gender === 'male' ? 'ذكر' : 'أنثى',
                    'phone'         => $patient->phone ?? 'غير متوفر',
                    'blood_group'   => $patient->blood_group ?? 'O+',
                    'last_visit'    => Carbon::parse($patient->last_visit_date)->translatedFormat('d أكتوبر Y'),
                    'condition'     => 'مستقرة', 
                    'medical_notes' => $patient->medical_notes ?? 'لا توجد حساسية طبية أو ملاحظات مسجلة للمريض.'
                ],
                'tests_history' => $formattedTests
            ];

            return ApiResponseService::success($responseData, 'تم تحميل الملف الطبي للمريض بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء تحميل بروفايل المريض', 500);
        }
    }
}