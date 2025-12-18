<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public string $otp)
    {
        
    }

    public function build()
    {
         return $this->subject('رمز التحقق - تسجيل حساب جديد')
            ->view('emails.registration_otp')
            ->with(['otp' => $this->otp]);
    }
    
}
