<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterTest> $masterTests
 * @property-read int|null $master_tests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $staff
 * @property-read int|null $staff_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Laboratory extends Model
{
    protected $fillable = [
        'name', 
        'address', 
        'logo', 
        'phone', 
        'slot_interval', 
        'status', 
        'license_number'
    ];

    protected static function booted(): void
    {
        static::created(function (Laboratory $laboratory) {
            
            $schedules = [];
            $now = now();

           
            for ($i = 0; $i < 7; $i++) {
                $schedules[] = [
                    'lab_id'      => $laboratory->id,
                    'day_of_week' => $i, 
                    'start_time'  => '08:00:00',
                    'end_time'    => '16:00:00',
                    'is_day_off'  => ($i == 5) ? true : false,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            \App\Models\LabSchedule::insert($schedules);
        });
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'lab_id');
    }

    // العلاقات المخصصة (باستخدام Spatie)
    public function manager() 
    { 
        return $this->hasOne(User::class, 'lab_id')->role('lab_manager'); 
    }
    public function staff() 
    { 
        return $this->hasMany(User::class, 'lab_id')->role(['receptionist', 'lab_assistant']); 
    }


    // public function masterTests()
    // {
    //     return $this->belongsToMany(MasterTest::class, 'lab_tests', 'lab_id', 'master_test_id')
    //                 ->withPivot('price', 'is_available')
    //                 ->withTimestamps();
    // }


    public function ratings() {
        return $this->hasMany(LabRating::class, 'lab_id');
    }

    // دالة سحرية لحساب متوسط التقييم بسرعة
    public function averageRating() {
        return $this->ratings()->avg('rating');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'lab_id');
    }

    
    public function getTotalRevenueAttribute()
    {
        return $this->subscriptions()->where('status', 'paid')->sum('amount');
    }

    
    public function employees() {
        return $this->hasMany(EmployeeProfile::class);
    }

    
    public function patients() {
        return $this->belongsToMany(User::class, 'laboratory_patient')
                    ->withPivot('internal_patient_number')
                    ->withTimestamps();
    }

    public function masterTests()
    {
        return $this->belongsToMany(MasterTest::class, 'lab_tests', 'lab_id', 'master_test_id')
                    ->withPivot('price', 'estimated_time_hours')
                    ->withTimestamps();
    }

}
