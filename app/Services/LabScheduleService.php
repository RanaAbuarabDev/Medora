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

    
    public function updateSchedule(int $labId, array $schedulesData): void
    {
        foreach ($schedulesData as $dayData) {
            
            \App\Models\LabSchedule::updateOrCreate(
                [
                    'lab_id'      => $labId,
                    'day_of_week' => $dayData['day_of_week'],
                ],
                [
                    'is_day_off'  => $dayData['is_day_off'] ?? false, // ⚡ تم التعديل إلى is_day_off
                    'start_time'  => $dayData['is_day_off'] ? null : $dayData['start_time'], // ⚡ تم التعديل إلى start_time
                    'end_time'    => $dayData['is_day_off'] ? null : $dayData['end_time'],   // ⚡ تم التعديل إلى end_time
                ]
            );
        }
    }

    public function getLabAssistantsCount(int $labId): int
    {
        return User::where('lab_id', $labId)->role('lab_assistant')->count();
    }
}