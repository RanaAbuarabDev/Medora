<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleResult extends Model
{
    protected $fillable = ['appointment_id', 'parameter_id', 'result_value', 'employee_id', 'status'];

    // النتيجة تنتمي لموعد محدد
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    // النتيجة تابعة لفحص فرعي محدد لمعرفة الـ Min والـ Max والوحدة
    public function parameter()
    {
        return $this->belongsTo(TestParameter::class, 'parameter_id');
    }

    // النتيجة تم إدخالها بواسطة موظف مخبري محدد (رشا)
    public function technician()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }
}
