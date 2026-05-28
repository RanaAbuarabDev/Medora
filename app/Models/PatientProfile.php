<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    protected $fillable = ['user_id', 'gender', 'birth_date', 'emergency_phone', 'address', 'medical_notes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
