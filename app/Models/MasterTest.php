<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $test_category_id
 * @property string $name
 * @property string|null $short_name
 * @property string|null $sample_type
 * @property string|null $unit
 * @property string|null $normal_range
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TestCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Laboratory> $laboratories
 * @property-read int|null $laboratories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereNormalRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereSampleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereTestCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
                    ->withPivot('price', 'estimated_time_hours', 'is_available') 
                    ->withTimestamps();
    }
}
