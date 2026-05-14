<?php

namespace App\Repositories;

use App\Models\LabSchedule;
use App\Repositories\Interfaces\LabScheduleRepositoryInterface;
use Illuminate\Support\Collection;

class LabScheduleRepository implements LabScheduleRepositoryInterface
{
    public function getByLab(int $labId): Collection
    {
        return LabSchedule::where('lab_id', $labId)
            ->orderBy('day_of_week')
            ->get();
    }

    public function updateOrCreate(int $labId, int $dayOfWeek, array $data): LabSchedule
    {
        return LabSchedule::updateOrCreate(
            [
                'lab_id'      => $labId,
                'day_of_week' => $dayOfWeek,
            ],
            
            array_merge($data, [
                'lab_id'      => $labId,
                'day_of_week' => $dayOfWeek,
            ])
        );
    }
}


