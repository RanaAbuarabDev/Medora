<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Laboratory|null $lab
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabSchedule query()
 * @mixin \Eloquent
 */
class LabSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'is_day_off',
        'lab_id'
    ];

    protected $casts = [
        'is_day_off' => 'boolean',
    ];

    public function lab()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }
}
