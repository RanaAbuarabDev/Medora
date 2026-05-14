<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('تذكير بسداد اشتراك شهري - منصة ميدورا')
            ->line('نود تذكيركم بأن اشتراك شهر ' . now()->format('Y-m') . ' لم يتم سداده بعد.')
            ->line('يرجى التواصل مع الإدارة لتأكيد الدفع كاش لضمان استمرار الخدمة.')
            ->action('عرض الفواتير', url('/dashboard/payments'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'لديك فاتورة اشتراك معلقة لشهر ' . now()->format('Y-m'),
            'amount' => 50
        ];
    }
}
