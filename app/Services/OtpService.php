<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationOtpMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;

class OtpService
{
    public function send(string $email, string $type): void
    {
        $otp = random_int(100000, 999999);

        DB::table('otps')->where([
            'email' => $email,
            'type' => $type
        ])->delete();

        DB::table('otps')->insert([
            'email' => $email,
            'otp' => bcrypt($otp),
            'type' => $type,
            'created_at' => now(),
        ]);

        Mail::to($email)->send(
            new RegistrationOtpMail($otp)
        );

    }

    public function verify(string $email, string $otp, string $type): bool
    {
        $record = DB::table('otps')
            ->where('email', $email)
            ->where('type', $type)
            ->first();

        if (! $record) return false;

        if (now()->diffInMinutes($record->created_at) > 5) {
            return false;
        }

        if (!password_verify($otp, $record->otp)) {
            return false;
        }

        DB::table('otps')->where('email', $email)->delete();

        return true;
    }
}
