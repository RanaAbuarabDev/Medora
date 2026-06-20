<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\OtpAttemptService;
use App\Services\OtpVerificationService;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $authService;
    protected $otpService;
    protected $attemptService;

    public function __construct(
        AuthService $authService, 
        OtpService $otpService, 
        OtpAttemptService $otpAttemptService
    ) {
        $this->authService = $authService;
        $this->otpService = $otpService;
        $this->attemptService = $otpAttemptService;
    }

    /**
     * تسجيل الدخول وإرجاع الأدوار والمعلومات كاملة
     */
    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->validated());

            // ⚡ هنا التعديل الجوهري: نمرر مصفوفة $data['user'] كاملة وبداخلها الـ roles تلقائياً
            return ApiResponseService::success([
                'user' => $data['user'], 
                'authorisation' => [
                    'token' => $data['token'],
                    'type'  => 'bearer'
                ]
            ], "تم تسجيل الدخول بنجاح", 200);

        } catch (\Exception $e) {
            // التقاط رمز الخطأ إذا كان حساب محظور (403) أو خطأ بيانات (401)
            $statusCode = $e->getCode() == 403 ? 403 : 401;
            return ApiResponseService::error([$e->getMessage()], 'فشل تسجيل الدخول', $statusCode);
        }
    }

    /**
     * تسجيل مريض جديد وإرسال رمز التحقق
     */
    public function registerPatient(RegisterRequest $request)
    {
        $this->authService->registerPatient($request->validated());

        return ApiResponseService::success([], 'تم إرسال رمز التحقق إلى بريدك الإلكتروني', 201);
    }

    /**
     * التحقق من رمز التسجيل (Email Verification)
     */
    public function verifyRegistrationOtp(Request $request, OtpVerificationService $otpVerificationService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string'
        ]);

        $result = $otpVerificationService->verify($request->email, $request->otp);

        if (!$result['success']) {
            return ApiResponseService::error($result['message'], 422);
        }

        return ApiResponseService::success([
            'token' => $result['token'],
            'type'  => $result['type']
        ], 'تم التحقق من البريد الإلكتروني بنجاح');
    }

    /**
     * 1. نسيان كلمة المرور: إرسال الرمز
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiResponseService::error('هذا الحساب غير موجود لدينا، يرجى التأكد من البريد الإلكتروني.', 404);
        }

        $this->otpService->send($request->email, 'password_reset');

        return ApiResponseService::success(null, 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.');
    }

    /**
     * 2. التحقق من الرمز لتغيير كلمة المرور
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string'
        ]);

        $email = $request->email;

        if ($this->attemptService->isLocked($email)) {
            $minutes = ceil($this->attemptService->remainingTime($email) / 60);
            return ApiResponseService::error("تم حظرك مؤقتاً. حاول بعد {$minutes} دقيقة.", 429);
        }

        if (!$this->otpService->verify($email, $request->otp, 'password_reset')) {
            $this->attemptService->increment($email);
            
            if ($this->attemptService->exceeded($email)) {
                $this->attemptService->lock($email);
                return ApiResponseService::error("تجاوزت المحاولات المسموحة. تم قفل العملية مؤقتاً.", 429);
            }

            return ApiResponseService::error("رمز التحقق غير صحيح.", 400);
        }

        $this->attemptService->clear($email);

        return ApiResponseService::success(null, 'تم التحقق بنجاح، يمكنك الآن تعيين كلمة مرور جديدة.');
    }

    /**
     * 3. تعيين كلمة المرور الجديدة
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponseService::success([
            'token' => $token,
            'user'  => $user
        ], 'تم تغيير كلمة المرور بنجاح وتسجيل دخولك.');
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success(null, 'تم تسجيل الخروج بنجاح');
    }
}