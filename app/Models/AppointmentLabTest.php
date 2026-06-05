<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AppointmentLabTest extends Pivot
{
    // تحديد اسم الجدول في قاعدة البيانات بدقة
    protected $table = 'appointment_lab_test';

    // السماح بالتعبئة الكتلية للحقول الأساسية والإضافية
    protected $fillable = [
        'appointment_id',
        'lab_test_id', // أو master_test_id حسب التسمية لديك في السكيما لربطه بالتحليل
        'result_value',
        'status'
    ];

    /**
     * علاقة عكسية: النتيجة تتبع لتحليل رئيسي معين (Master Test)
     */
    public function masterTest()
    {
        // استبدلي MasterTest::class بالموديل المسؤول عن أسماء التحاليل والحدود لديكِ
        return $this->belongsTo(MasterTest::class, 'lab_test_id'); 
    }

    /**
     * علاقة عكسية: هذه النتيجة تتبع لموعد أو حجز محدد
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}