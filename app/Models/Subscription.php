<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    

    protected $fillable = [
        'lab_id',
        'invoice_number',
        'amount',
        'billing_month',
        'status',
        'paid_at'
    ];


    protected $casts = [
        'paid_at' => 'datetime',
        'billing_month' => 'string', 
    ];
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    
    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function lab()
    {
        return $this->belongsTo(User::class, 'lab_id');
    }
}
