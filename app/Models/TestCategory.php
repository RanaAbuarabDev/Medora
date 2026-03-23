<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TestCategory extends Model
{
    protected $fillable = ['name', 'icon','description'];

    public function masterTests()
    {
        return $this->hasMany(MasterTest::class, 'test_category_id');
    }
}
