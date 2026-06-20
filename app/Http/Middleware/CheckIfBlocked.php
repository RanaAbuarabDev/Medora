<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
use Symfony\Component\HttpFoundation\Response;

class CheckIfBlocked
{
    /**
     * اعتراض الطلبات وفحص حالة الموظف
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التأكد من أن المستخدم مسجل دخوله حالياً
        if (auth()->check() && auth()->user()->is_blocked) {
            
            // احترافي جداً: حذف الـ Token الحالي لكي يتم طرده من واجهة الفرونت إند مباشرة
            if (method_exists($request->user(), 'currentAccessToken')) {
                $request->user()->currentAccessToken()->delete();
            }

            return ApiResponseService::error(
                ['account' => 'تم حظر هذا الحساب من قِبل إدارة المختبر.'], 
                'عذراً، لم يعد لديك صلاحية الوصول للنظام', 
                403
            );
        }

        return $next($request);
    }
}