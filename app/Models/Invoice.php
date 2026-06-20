<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'total_amount',
        'amount_paid',
        'payment_status',
        'lab_id',
        'patient_id',
    ];

    
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    public function appointment()
    {
        return $this->belongsTo(Appointment::class,'appointment_id');
    }
}
