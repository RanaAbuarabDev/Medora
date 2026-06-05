<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
  
        'user_id',
        'lab_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'cancel_reason',
        'sample_code'

        
    ];


    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED_BY_PATIENT = 'cancelled_by_patient';
    const STATUS_CANCELLED_BY_LAB = 'cancelled_by_lab';
   
    public function patient() {
        return $this->belongsTo(User::class, 'user_id');
    }



    public function labTests()
    {
        return $this->belongsToMany(LabTest::class, 'appointment_lab_test', 'appointment_id', 'lab_test_id')
                    ->withPivot('id', 'result_value', 'status')
                    ->withTimestamps();
    }
 
    public function test()
    {
        // تأكدي من اسم مودل التحاليل عندك (Test أو MedicalTest)
        return $this->belongsTo(LabTest::class, 'test_id');
    }

    public function lab() {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    /**
     * Accessor: لحساب عدد الأيام المتبقية للموعد تلقائياً
     * بنناديها بالكود هيك: $appointment->days_until
     */
    public function getDaysUntilAttribute()
    {
        $date = Carbon::parse($this->appointment_date);
        $now = Carbon::today();
        
        return $now->diffInDays($date, false); 
    }

    public function rating()
    {
        return $this->hasOne(LabRating::class);
    }


    // الموعد يملك فاتورة واحدة فقط
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }


    

    // 2. جلب جميع النتائج الرقمية التي أدخلها المساعد لهذا الموعد (One-to-Many)
    public function sampleResults()
    {
        return $this->hasMany(SampleResult::class, 'appointment_id');
    }

    // 3. علاقة الموعد بملف المريض (BelongsTo)
    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id'); // تأكدي من اسم الفورين كي لديكِ
    }
}