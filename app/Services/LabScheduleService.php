<?php

namespace App\Services;

use App\Models\LabSchedule;
use App\Models\User;
use App\Repositories\Interfaces\LabScheduleRepositoryInterface;

class LabScheduleService
{
    public function __construct(
        protected LabScheduleRepositoryInterface $repository
    ) {}

    public function getSchedule(int $labId)
    {
        return $this->repository->getByLab($labId);
    }

    public function updateSchedule(int $labId, int $dayOfWeek, array $data): LabSchedule
    {
        
        return LabSchedule::updateOrCreate(
            [
                'lab_id'      => $labId,
                'day_of_week' => $dayOfWeek,
            ],
            $data 
        );
    }

    public function getLabAssistantsCount(int $labId): int
    {
        return User::where('lab_id', $labId)->role('lab_assistant')->count();
    }
}