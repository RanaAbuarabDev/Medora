<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\IndexPatientRequest;
use App\Services\LabManager\LabPatientService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Exception;

class LabPatientController extends Controller
{
    protected $patientService;

    // حقن السيرفس عبر الـ Constructor بطريقة احترافية
    public function __construct(LabPatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * عرض لوحة تحكم سجلات المرضى للمخبر الحالي
     */
    public function index(IndexPatientRequest $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            if (!$labId) {
                return ApiResponseService::error(null, 'المستخدم غير مرتبط بمخبر معتمد', 403);
            }

            // 1. جلب إجمالي عدد المرضى للكارت العلوي
            $totalPatients = $this->patientService->getTotalPatientsCount($labId);

            // 2. جلب التوزيع الفئوي للأعمار (الرسم البياني بالأسفل)
            $ageStats = $this->patientService->getAgeDistribution($labId);

            // 3. جلب قائمة المرضى المقسمة لصفحات بناءً على الفلاتر
            $paginator = $this->patientService->getPaginatedPatients($labId, $request->validated());

            // 4. تنسيق (Mapping) الأسطر لتخرج مطابقة للفرونت إند بالمليمتر
            $formattedPatients = collect($paginator->items())->map(function ($row) {
                $lastVisit = Carbon::parse($row->last_visit_date);
                
                return [
                    'id'               => $row->id,
                    'name'             => $row->name,
                    'avatar_letters'   => mb_substr($row->name, 0, 2), // لرسم الدائرة الرمزية بالفرونت (مثل: سو، مح)
                    'age'              => $row->birth_date ? Carbon::parse($row->birth_date)->age . ' عاماً' : 'غير محدد',
                    'gender'           => $row->gender == 'male' || $row->gender == 'ذكر' ? 'ذكر' : 'أنثى',
                    'phone'            => $row->phone ?? '050XXXXXXX',
                    'last_visit'       => $lastVisit->translatedFormat('d F Y'), // يعود بصيغة "12 أكتوبر 2023" متطابق مع اللقطة
                    'total_tests'      => $row->total_appointments_count . ' فحوصات',
                ];
            });

            // 5. دمج المكونات وإرسالها
            $responseData = [
                'total_patients_count' => $totalPatients,
                'age_distribution'     => $ageStats,
                'patients'             => $formattedPatients,
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages'  => $paginator->lastPage()
                ]
            ];

            return ApiResponseService::success($responseData, 'تم جلب سجل المرضى للمخبر بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ غير متوقع أثناء معالجة بيانات المرضى', 500);
        }
    }
}