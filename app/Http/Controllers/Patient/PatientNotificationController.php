<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class PatientNotificationController extends Controller
{
    protected $apiResponse;

    public function __construct(ApiResponseService $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    /**
     * 1. جلب كافة الإشعارات الخاصة بالمريض (المقروءة وغير المقروءة)
     * GET /api/patient/notifications
     */
    public function index(Request $request)
    {
        try {
            // جلب المستخدم المريض المسجل دخوله حالياً عبر الـ Token
            $patient = $request->user();

            // جلب الإشعارات وعمل Pagination لها (مثلاً 10 إشعارات في الصفحة) لضمان سرعة السيرفر
            $notifications = $patient->notifications()->paginate(10);

            // تنسيق الرد للفرونت إند مع حساب عدد الإشعارات غير المقروءة للعداد (Badge)
            $unreadCount = $patient->unreadNotifications()->count();

            $responseData = [
                'unread_count'  => $unreadCount,
                'notifications' => $notifications->map(function ($notification) {
                    return [
                        'id'         => $notification->id,
                        'read_at'    => $notification->read_at,
                        'is_read'    => !is_null($notification->read_at),
                        // هنا جيسون التقرير الطبي الكامل والعبارات المناسبة التي صممناها سابقاً
                        'data'       => $notification->data, 
                        'created_at' => $notification->created_at->toIso8601String(),
                    ];
                }),
                // معلومات الصفحات للفرونت إند للـ Scrolling أو الـ Pagination
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'has_more'     => $notifications->hasMorePages(),
                ]
            ];

            return $this->apiResponse->success($responseData, 'تم جلب الإشعارات بنجاح.');

        } catch (\Exception $e) {
            return $this->apiResponse->error('فشل في جلب الإشعارات: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 2. تحويل إشعار معين إلى "مقروء" عند النقر عليه
     * POST /api/patient/notifications/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $patient = $request->user();
            
            // البحث عن الإشعار الخاص بهذا المريض حصراً لحماية البيانات
            $notification = $patient->notifications()->where('id', $id)->first();

            if (!$notification) {
                return $this->apiResponse->error('الإشعار غير موجود أو لا تملك صلاحية الوصول إليه.', 404);
            }

            // تحويله لمقروء ب ميزة لارافل المدمجة
            $notification->markAsRead();

            return $this->apiResponse->success(null, 'تم تعيين الإشعار كمقروء بنجاح.');

        } catch (\Exception $e) {
            return $this->apiResponse->error('فشل في تحديث حالة الإشعار: ' . $e->getMessage(), 500);
        }
    }
}