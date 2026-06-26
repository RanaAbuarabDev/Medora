<?php

namespace App\Notifications\LabManager;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // 👈 استيراد الإنترفيس السحري
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue // 👈 قمنا بعمل implements هنا
{
    use Queueable; // تتيح التحكم بالـ Queue والـ Delay

    protected $itemName;
    protected $currentQuantity;

    public function __construct($itemName, $currentQuantity)
    {
        $this->itemName = $itemName;
        $this->currentQuantity = $currentQuantity;
    }

    public function via($notifiable): array
    {
        return ['database']; // سيتم حفظه بالخلفية داخل جدول الـ notifications
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => '🚨 تنبيه: انخفاض في المخزون!',
            'message' => "لقد انخفض مخزون المادة ({$this->itemName}) إلى {$this->currentQuantity} قطع. يرجى إعادة التعبئة فوراً.",
            'action_url' => '/lab/inventory'
        ];
    }
}