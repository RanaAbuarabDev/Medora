<?php

namespace App\Repositories\Interfaces;

use App\Models\LabSchedule;
use Illuminate\Support\Collection;

interface LabScheduleRepositoryInterface
{
    public function getByLab(int $labId): Collection;
    public function updateOrCreate(int $labId, int $dayOfWeek, array $data): LabSchedule;
}