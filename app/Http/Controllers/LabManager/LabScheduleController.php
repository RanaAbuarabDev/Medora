<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Requests\LabScheduleRequest;
use App\Http\Resources\LabScheduleResource;
use App\Services\LabScheduleService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Controllers\Controller;

class LabScheduleController extends Controller
{
    /**
     * حقن السيرفس عبر الـ Constructor 
     */
    public function __construct(
        protected LabScheduleService $service
    ) {}

    /**
     * عرض إعدادات المواعيد وجدول أوقات الدوام بالكامل للمخبر الحالي
     * GET /api/lab/schedule
     */
    public function index(): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            
            // جلب الـ lab_id الخاص بالمستخدم الحالي المتصل
            $labId = $user->lab_id ?? ($user->laboratory ? $user->laboratory->id : null);

            if (!$labId) {
                return ApiResponseService::error(null, 'هذا المستخدم غير مرتبط بمختبر معتمد', 403);
            }

            // 1. جلب جدول المواعيد الأسبوعي
            $schedules = $this->service->getSchedule($labId);

            // 2. جلب عدد المساعدين المخبريين المتوفرين حالياً للكارت العلوي
            $assistantsCount = $this->service->getLabAssistantsCount($labId);

            // 3. جلب المختبر لقراءة مدة الموعد الحقيقية بقاعدة البيانات
            $laboratory = $user->laboratory;

            // 4. تجميع البيانات لتطابق مكونات الواجهة بالكامل (الكروت + الجدول)
            $responseData = [
                'appointment_settings' => [
                    'default_duration'        => $laboratory ? $laboratory->slot_interval : 15, // ⚡ تم التعديل إلى slot_interval ديناميكياً
                    'active_assistants_count' => $assistantsCount, // يغذي كارت "4 مساعدين متوفرين"
                ],
                'working_days' => LabScheduleResource::collection($schedules)
            ];

            return ApiResponseService::success($responseData, 'تم تحميل إعدادات وجدول مواعيد المخبر بنجاح');

        } catch (Exception $e) {
            Log::error('LabSchedule index error: ' . $e->getMessage());
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء جلب إعدادات الجدول', 500);
        }
    }

    /**
     * تحديث أوقات الدوام للأسبوع كاملاً مع مدة الموعد بنقرة واحدة
     * PUT/POST /api/lab/schedule/update-all
     */
    public function update(LabScheduleRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $labId = $user->lab_id ?? ($user->laboratory ? $user->laboratory->id : null);

            if (!$labId) {
                return ApiResponseService::error(null, 'هذا المستخدم غير مرتبط بمختبر معتمد', 403);
            }

            // 1. تحديث مدة الموعد (slot_interval) بجدول المختبرات إذا تم إرسالها من الواجهة
            if ($request->has('slot_interval') && $user->laboratory) {
                $user->laboratory->update([
                    'slot_interval' => $request->input('slot_interval') // ⚡ حفظ المسمى الحقيقي بقاعدة بياناتكِ
                ]);
            }

            // 2. تمرير مصفوفة الأيام القادمة من الريكويست ليتم حفظها بداخل السيرفس
            $this->service->updateSchedule($labId, $request->input('schedules', []));

            DB::commit();
            return ApiResponseService::success(null, 'تم حفظ وتحديث جدول أوقات الدوام بنجاح');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('LabSchedule update error: ' . $e->getMessage());
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ غير متوقع أثناء حفظ التعديلات', 500);
        }
    }
}