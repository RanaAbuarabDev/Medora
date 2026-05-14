<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Services\AppointmentService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected $service;

    public function __construct(AppointmentService $service)
    {
        $this->service = $service;
    }

    // حجز موعد جديد
    public function store(StoreAppointmentRequest $request)
    {
        try {
            $appointment = $this->service->storeAppointment($request->validated());
            return ApiResponseService::success($appointment, 'تم تسجيل طلب الحجز بنجاح.');
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), 422);
        }
    }

    // إلغاء موعد
    public function cancel(Request $request, $id)
    {
        try {
            $appointment = $this->service->cancelAppointment($id, $request->cancel_reason);
            return ApiResponseService::success($appointment, 'تم إلغاء الموعد.');
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), 422);
        }
    }


    // App\Http\Controllers\AppointmentController.php

    // عرض كل المواعيد (القادمة والسابقة)
    public function index()
    {
        $data = $this->service->getPatientAppointments(Auth::id());
        return ApiResponseService::success($data, 'تم جلب المواعيد بنجاح.');
    }

    // عرض تفاصيل موعد واحد
    public function show($id)
    {
        try {
            $appointment = $this->service->getAppointmentDetails($id, Auth::id());
            
            // إضافة حقل الأيام المتبقية يدوياً للرد (Response) للتأكيد
            $appointment['days_left'] = $appointment->days_until; 

            return ApiResponseService::success($appointment, 'تم جلب تفاصيل الموعد.');
        } catch (Exception $e) {
            return ApiResponseService::error('الموعد غير موجود أو غير مصرح لك بالعرض', 404);
        }
    }
}