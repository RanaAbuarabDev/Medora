<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTest extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(TestCategory::class, 'test_category_id');
    }


    public function laboratories()
    {
        return $this->belongsToMany(Laboratory::class, 'lab_tests', 'master_test_id', 'lab_id')
                    ->withPivot('price', 'is_available')
                    ->withTimestamps();
    }
}
