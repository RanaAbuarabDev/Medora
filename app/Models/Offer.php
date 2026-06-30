<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Offer extends Model
{
    protected $fillable = [
        'lab_id', 'lab_test_id', 'category_id', 'name', 
        'discount_percentage', 'start_date', 'end_date', 'is_active'
    ];

    /**
     * Scope ذكي يجلب العروض الفعالة اليوم تلقائياً
     */
    public function scopeActiveToday(Builder $query)
    {
        $today = Carbon::today()->format('Y-m-d');
        
        return $query->where('is_active', true)
                     ->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }
}