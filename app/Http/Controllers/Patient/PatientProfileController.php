<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Patient\UpdateProfileRequest;
use App\Http\Requests\Patient\UpdateProfileRequest as PatientUpdateProfileRequest;
use App\Services\ApiResponseService;
use App\Services\Patient\PatientProfileService;
use Exception;

class PatientProfileController extends Controller
{
    protected $profileService;

    public function __construct(PatientProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * جلب بيانات حساب المريض الحالية لعرضها في الواجهة
     * GET /api/patient/profile
     */
    public function show()
    {
        try {
            $data = $this->profileService->getProfileData();
            return ApiResponseService::success($data, 'تم جلب بيانات الحساب بنجاح.');
        } catch (Exception $e) {
            return ApiResponseService::error('فشل في جلب البيانات: ' . $e->getMessage(), 500);
        }
    }

    /**
     * تحديث بيانات حساب المريض والملاحظات الطبية
     * POST /api/patient/profile/update
     */
    public function update(PatientUpdateProfileRequest $request)
    {
        try {
            // تمرير البيانات المحققة والآمنة فقط للسيرفس
            $updatedData = $this->profileService->updateProfile($request->validated());

            return ApiResponseService::success($updatedData, 'تم تحديث ملفك الشخصي بنجاح المظهر في الواجهة.');
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), 422);
        }
    }
}