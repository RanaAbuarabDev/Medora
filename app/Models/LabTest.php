<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $lab_id
 * @property int $master_test_id
 * @property numeric $price
 * @property int $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereLabId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereMasterTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LabTest extends Model
{
    //
}
