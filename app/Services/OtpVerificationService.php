<?php


namespace App\Services;

use App\Models\User;

class OtpVerificationService
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    public function verify(string $email, string $otp): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ];
        }

        if (! $this->otpService->verify($email, $otp, 'registration')) {
            return [
                'success' => false,
                'message' => 'رمز التحقق غير صالح أو منتهي'
            ];
        }

        // تفعيل الحساب
        $user->update([
            'email_verified_at' => now()
        ]);

        // إنشاء توكن Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'token' => $token,
            'type' => 'bearer'
        ];
    }
}
