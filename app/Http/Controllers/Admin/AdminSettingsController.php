<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSettingsController extends Controller {

    public function index() {
        
        $settings = Setting::all()->pluck('value', 'key'); 
        
        
        $user = auth()->user();

        return ApiResponseService::success([
            'system_settings' => $settings,
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar, // تأكدي من إضافة الحقل كما اتفقنا
            ]
        ], 'تم جلب الإعدادات والبروفايل بنجاح');
    }

    public function update(Request $request) {
        // 1. التحقق من المدخلات بناءً على الصورة image_be2cf4.png
        $validated = $request->validate([
            // إعدادات المنصة
            'site_name' => 'required|string',
            'contact_email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            'support_phone' => 'nullable|string',
            
            
            'default_subscription_price' => 'required|numeric',
            'trial_period_days' => 'required|integer',
            
            
            'maintenance_mode' => 'required|boolean',
            'system_notifications' => 'required|boolean',

            
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
        ]);

       
        $systemKeys = [
            'site_name', 'contact_email', 'support_phone', 
            'default_subscription_price', 'trial_period_days', 
            'maintenance_mode', 'system_notifications'
        ];

        foreach ($systemKeys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
            }
        }

        // 3. تحديث بيانات المستخدم (البروفايل)
        $user = auth()->user();
        if ($request->hasAny(['name', 'email'])) {
            $user->update($request->only(['name', 'email']));
        }

        // 4. معالجة وضع الصيانة (كما فعلنا سابقاً)
        if ($request->has('maintenance_mode')) {
            $isMaintenance = filter_var($request->maintenance_mode, FILTER_VALIDATE_BOOLEAN);
            $isMaintenance 
                ? \Illuminate\Support\Facades\Artisan::call('down --secret="medora-safe-access"') 
                : \Illuminate\Support\Facades\Artisan::call('up');
        }

        return ApiResponseService::success(null, 'تم حفظ جميع التغييرات بنجاح');
    }

    // دالة منفصلة لتغيير كلمة المرور (لأن لها زر منفصل في الصورة)
    public function updatePassword(Request $request) {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:8',
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return ApiResponseService::success(null, 'تم تحديث كلمة المرور بنجاح');
    }
}