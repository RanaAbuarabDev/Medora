<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterTest> $masterTests
 * @property-read int|null $master_tests_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestCategory extends Model
{
    protected $fillable = ['name', 'icon','description'];

    public function masterTests()
    {
        return $this->hasMany(MasterTest::class, 'test_category_id');
    }
}
