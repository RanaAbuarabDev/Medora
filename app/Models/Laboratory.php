<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = ['name','address','logo','phone'];


    // العلاقة العامة (كل من يتبع للمخبر)
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
}
