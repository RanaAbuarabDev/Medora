<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Services\Reception\ReceptionistDashboardService;
use App\Services\ApiResponseService;
use App\Http\Resources\Reception\DailyAppointmentResource;
use App\Http\Requests\Reception\BookAppointmentRequest; // ⚡ استيراد الـ Request الجديد
use App\Http\Resources\Reception\AppointmentManagementResource;
use App\Http\Resources\Reception\PatientMedicalProfileResource;
use Illuminate\Http\Request;

class ReceptionistDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(ReceptionistDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Endpoint الرئيسي للوحة التحكم
     */
    public function index(Request $request)
    {
        $employeeProfile = $request->user()->employeeProfile;

        if (!$employeeProfile) {
            return ApiResponseService::error('هذا المستخدم ليس لديه بروفايل موظف صلاحي!', 403);
        }

        $labId = $employeeProfile->laboratory_id; 

        $data = $this->dashboardService->getDashboardData($labId);
        $data['appointments'] = DailyAppointmentResource::collection($data['appointments']);

        return ApiResponseService::success($data, 'تم جلب بيانات لوحة التحكم بنجاح');
    }

    /**
     * Endpoint التبديل السريع للدفع (Toggle Payment)
     */
    public function togglePayment(int $invoiceId)
    {
        try {
            $invoice = $this->dashboardService->togglePaymentStatus($invoiceId);
            
            return ApiResponseService::success([
                'invoice_id'     => $invoice->id,
                'payment_status' => $invoice->payment_status
            ], 'تم تحديث الحالة المالية بنجاح');
            
        } catch (\Exception $e) {
            return ApiResponseService::error('فشل تحديث الحالة المالية: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ⚡ Endpoint حجز موعد جديد وتسجيل مريض (مع الـ Form Request والنظام الموحد)
     */
    public function bookAppointment(BookAppointmentRequest $request)
    {
        // جلب معرف المختبر من الموظف الحالي لضمان الأمان
        $labId = $request->user()->lab_id ?? 1;

        try {
            // نمرر البيانات التي تم التحقق منها ونقحتها الـ Form Request بنجاح
            $appointment = $this->dashboardService->storeAppointment($request->validated(), $labId);
            
            // رد موحد واحترافي للفرونت إند عند نجاح العملية
            return ApiResponseService::success($appointment, 'تم تسجيل الموعد بنجاح وتوليد الفاتورة التابعة له', 201);
        } catch (\Exception $e) {
            return ApiResponseService::error('فشل إتمام عملية الحجز: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Endpoint جلب الأوقات المتاحة للمختبر بناءً على تاريخ محدد
     */
    public function getSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $labId = $request->user()->lab_id ?? 1; 
        $date  = $request->date;

        $slots = $this->dashboardService->getAvailableSlots($labId, $date);

        return ApiResponseService::success($slots, 'تم جلب الأوقات المتاحة بنجاح');
    }


    
    public function searchPatient(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2', // البحث يبدأ من حرفين أو رقمين على الأقل
        ]);

        // جلب النص القادم من الفرونت إند (مثال: ?query=0933)
        $searchQuery = $request->query('query');

        $patients = $this->dashboardService->searchPatients($searchQuery);
        if ($patients->isEmpty()) {
            return ApiResponseService::success(
                [], 
                'المريض غير مسجل في النظام مسبقاً، يمكنك إضافة مريض جديد.' 
            );
        }

        return ApiResponseService::success($patients, 'تم جلب نتائج البحث بنجاح');
    }


    public function manageAppointments(Request $request)
    {
        $employeeProfile = $request->user()->employeeProfile;

        if (!$employeeProfile) {
            return ApiResponseService::error('هذا المستخدم ليس لديه بروفايل موظف صلاحي!', 403);
        }

        $labId = $employeeProfile->laboratory_id;

        // تمرير كافة مدخلات الفلترة القادمة من الـ Query Params إلى السيرفيس
        $paginatedAppointments = $this->dashboardService->getManageAppointments($labId, $request->all());

        // تحويل البيانات عبر الـ Resource مع الحفاظ على مصفوفة الـ Pagination الأساسية
        $formattedData = AppointmentManagementResource::collection($paginatedAppointments)->response()->getData(true);

        return ApiResponseService::success($formattedData, 'تم جلب قائمة المواعيد بنجاح');
    }


    public function getPatientProfile($id)
    {
        // استدعاء السيرفيس لجلب بيانات المريض
        $patient = $this->dashboardService->getPatientMedicalProfile($id);

        // إرجاع البيانات مغلفة بالـ Resource المنسق للواجهة
        return ApiResponseService::success(
            new PatientMedicalProfileResource($patient), 
            'تم جلب بروفايل المريض الطبي بنجاح'
        );
    }
}