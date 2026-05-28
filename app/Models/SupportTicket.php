<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'laboratory_id',
        'patient_id',
        'message',
        'reply',
        'status',
    ];

    // الاستفسار ينتمي لمريض محدد
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // الاستفسار ينتمي لمختبر محدد
    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }
}
