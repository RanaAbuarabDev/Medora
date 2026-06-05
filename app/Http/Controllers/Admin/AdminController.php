<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{

    protected $AuthService;

    public function __construct(AuthService $authService)
    {
        $this->AuthService = $authService;
    }



   public function registerLabWithManager(Request $request) 
    {
        // 1. الفاليديشن (يفضل عمل Request خاص لهذا الأمر)
        $data = $request->validate([
            'lab_name' => 'required|string|max:255',
            'address'  => 'required|string',
            'manager_name'  => 'required|string',
            'manager_email' => 'required|email|unique:users,email',
            'manager_password' => 'required|min:8',
        ]);

        // 2. استدعاء الخدمة لتنفيذ العملية (Atomic Operation)
        $result = $this->AuthService->setupNewLab($data);

        return ApiResponseService::success(
            $result,
            'تم إنشاء المخبر وتعيين المدير بنجاح',
            201
        );
    }

    /**
 * حذف مخبر بالكامل من المنصة (Super Admin صلاحية)
 * DELETE /api/admin/laboratories/{id}
 */
    public function destroyLab($id)
    {
        try {
            // استدعاء الخدمة لتنفيذ الحذف الآمن
            $this->AuthService->deleteLaboratory($id);

            return ApiResponseService::success(
                null,
                'تم حذف المخبر وكافة الحسابات المرتبطة به بنجاح من المنصة.'
            );

        } catch (\Exception $e) {
            return ApiResponseService::error(
                'فشل في حذف المخبر: ' . $e->getMessage(),
                500
            );
        }
    }
}
