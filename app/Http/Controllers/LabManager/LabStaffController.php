<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\LabStaffStoreRequest;
use App\Services\LabManager\LabStaffService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Http\Requests\LabManager\LabStaffUpdateRequest;

class LabStaffController extends Controller
{
    protected $staffService;

    public function __construct(LabStaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    /**
     * عرض قائمة الموظفين مع الإحصائيات والفلاتر
     * GET /api/lab/staff
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            if (!$labId) {
                return ApiResponseService::error(null, 'المستخدم غير مرتبط بمخبر معتمد', 403);
            }

            // تجميع الفلاتر القادمة من ريكويست الفرونت إند
            $filters = [
                'search' => $request->query('search'),
                'role'   => $request->query('role'), // يرسل مثل: lab_assistant, receptionist
            ];

            // 1. جلب عدد الموظفين الإجمالي للكارت العلوي
            $totalStaff = $this->staffService->getStaffCount($labId);

            // 2. جلب الموظفين بالصفحة الحالية
            $paginator = $this->staffService->getPaginatedStaff($labId, $filters);

            // 3. قاموس لترجمة مسميات الأدوار لتطابق كبسولات الواجهة العربية
            $rolesMapping = [
                'lab_assistant' => 'فني مخبر',
                'receptionist'  => 'استقبال',
                'lab_manager'   => 'مدير مخبر'
            ];

            // 4. عمل Mapping لتنسيق البيانات الخارجة بالمليمتر للجدول
            $formattedStaff = collect($paginator->items())->map(function ($employee) use ($rolesMapping) {
                // جلب اسم أول دور مسند للموظف عبر Spatie
                $roleName = $employee->getRoleNames()->first(); 

                return [
                    'id'             => '#LAB-' . str_pad($employee->id, 2, '0', STR_PAD_LEFT), // كود الموظف #LAB-102
                    'name'           => $employee->name,
                    'avatar_letters' => mb_substr($employee->name, 0, 2), // الحرفين الأوائل للأفاتار الافتراضي
                    'email'          => $employee->email,
                    'phone'          => $employee->phone ?? 'غير مسجل',
                    'role_key'       => $roleName,
                    'role_title'     => $rolesMapping[$roleName] ?? 'موظف', // طباعة "فني مخبر" أو "استقبال"
                ];
            });

            // 5. بناء هيكل الـ Response النهائي الموحد
            $responseData = [
                'summary' => [
                    'total_staff_count' => $totalStaff, // يغذي الكارت بـ "إجمالي الموظفين: 12"
                    'avatars_preview'   => [
                        'https://via.placeholder.com/150', // روابط افتراضية لشغل الكارت جمالياً
                        'https://via.placeholder.com/150'
                    ]
                ],
                'staff_list' => $formattedStaff,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ]
            ];

            return ApiResponseService::success($responseData, 'تم تحميل قائمة الموظفين بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ غير متوقع أثناء معالجة بيانات الموظفين', 500);
        }
    }



    public function show(int $id): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $employee = $this->staffService->getStaffById($labId, $id);

            if (!$employee) {
                return ApiResponseService::error(null, 'الموظف غير موجود أو لا ينتمي لهذا المختبر', 404);
            }

            // تجهيز البيانات لتطابق حقول التصميم تماماً
            $responseData = [
                'id'         => '#LAB-' . $employee->id,
                'name'       => $employee->name,
                'email'      => $employee->email,
                'phone'      => $employee->phone ?? '',
                'role'       => $employee->getRoleNames()->first(), // يرجع القيمة الأساسية مثل lab_assistant
                'is_blocked' => (bool) $employee->is_blocked
            ];

            return ApiResponseService::success($responseData, 'تم تحميل تفاصيل الموظف بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء جلب التفاصيل', 500);
        }
    }

    /**
     * 2. تعديل بيانات الموظف ودوره (زر حفظ التغييرات)
     * PUT /api/lab/staff/{id}
     */
    public function update(LabStaffUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $employee = $this->staffService->getStaffById($labId, $id);

            if (!$employee) {
                return ApiResponseService::error(null, 'الموظف غير موجود', 404);
            }

            // تنفيذ التحديث من خلال السيرفس
            $this->staffService->updateStaff($employee, $request->validated());

            return ApiResponseService::success(null, 'تم تحديث بيانات الموظف ودوره الوظيفي بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء تحديث البيانات', 500);
        }
    }

    /**
     * 3. حظر أو إلغاء حظر الموظف (بديل زر الحذف في منطقة الخطر)
     * PATCH /api/lab/staff/{id}/toggle-block
     */
    /**
     * حظر أو إلغاء حظر الموظف
     */
    public function toggleBlock(int $id): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            $employee = $this->staffService->getStaffById($labId, $id);

            if (!$employee) {
                return ApiResponseService::error(null, 'الموظف غير موجود', 404);
            }

            // 1. تنفيذ التبديل والحفظ في قاعدة البيانات
            $updatedEmployee = $this->staffService->toggleBlock($employee);
            
            // ⚡ 2. تحديث بيانات الموديل فوراً لقراءة الحالة الجديدة المخزنة بالداتا بيز
            $updatedEmployee->refresh(); 

            // 3. التحقق من القيمة بعد التحديث الصريح
            if ($updatedEmployee->is_blocked == 1) {
                $isBlocked = true;
                $statusMessage = 'تم حظر الموظف وإيقاف صلاحيات وصوله بنجاح';
            } else {
                $isBlocked = false;
                $statusMessage = 'تم إلغاء حظر الموظف واستعادة صلاحياته بنجاح';
            }

            return ApiResponseService::success([
                'is_blocked' => $isBlocked
            ], $statusMessage);

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء معالجة الحظر', 500);
        }
    }


    /**
     * 4. إنشاء موظف جديد وتحديد دوره (واجهة إضافة موظف جديد)
     * POST /api/lab/staff
     */
    public function store(LabStaffStoreRequest $request): JsonResponse
    {
        try {
            // جلب معرف المخبر الخاص بمدير المختبر الحالي
            $labId = auth()->user()->lab_id;

            // تنفيذ عملية الإنشاء من خلال السيرفس
            $employee = $this->staffService->createStaff($labId, $request->validated());

            // إرجاع استجابة نجاح مع بيانات الموظف الأساسية للفرونت إند إذا احتاجوا عرضها فوراً
            $responseData = [
                'id'    => '#LAB-' . $employee->id,
                'name'  => $employee->name,
                'email' => $employee->email,
                'role'  => $request->role
            ];

            return ApiResponseService::success($responseData, 'تم إضافة الموظف الجديد وتحديد دوره الوظيفي بنجاح', 201);

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء إضافة الموظف', 500);
        }
    }
}