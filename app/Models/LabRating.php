<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabRating extends Model
{
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function laboratory() {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
