<?php

namespace App\Services\LabManager;

use App\Repositories\LabManager\LabAppointmentRepository;

class LabOperationService
{
    public function __construct(
        protected LabAppointmentRepository $repository
    ) {}

    public function getOperationsData(int $labId, array $filters)
    {
        $cards = $this->repository->getOperationCards($labId);
        $appointmentsPaginator = $this->repository->getFilteredAppointments($labId, $filters);

        return [
            'cards' => [
                'today_appointments_count' => $cards->today_count ?? 0,
                'completed_appointments_count' => $cards->completed_count ?? 0,
                'processing_appointments_count' => $cards->processing_count ?? 0,
            ],
            'appointments_paginator' => $appointmentsPaginator
        ];
    }
}