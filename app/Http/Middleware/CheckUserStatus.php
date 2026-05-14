<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->status !== 'active') {
            // تسجيل خروج المستخدم وإبطال التوكن
            auth()->user()->tokens()->delete(); 
            return response()->json(['message' => 'تم حظر حسابك، يرجى مراجعة الإدارة'], 403);
        }
        return $next($request);
    }
}
