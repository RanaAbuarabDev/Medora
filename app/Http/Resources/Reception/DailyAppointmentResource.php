<?php

namespace App\Http\Resources\Reception;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'patient_name'     => $this->patient->name ?? 'مريض غير معروف',
            'time'             => \Carbon\Carbon::parse($this->appointment_time)->format('H:i'),
            'period'           => \Carbon\Carbon::parse($this->appointment_time)->format('A') === 'AM' ? 'ص' : 'م',
            'status'           => $this->status, // waiting_sampling, processing, completed
            'status_label'     => $this->getStatusLabel(), // دالة مخصصة للنص العربي
            'tests'            => $this->labTests->map(function($test) {
                                    return [
                                        'id'   => $test->id,
                                        'code' => $test->code,
                                        'name' => $test->name
                                    ];
                                  }),
            'invoice'          => $this->invoice ? [
                                    'id'             => $this->invoice->id,
                                    'total_amount'   => number_format($this->invoice->total_amount, 0),
                                    'payment_status' => $this->invoice->payment_status, // paid, unpaid
                                  ] : null
        ];
    }

    private function getStatusLabel(): string
    {
        return match($this->status) {
            'waiting_sampling' => 'بانتظار السحب',
            'processing'       => 'قيد التحليل داخل المختبر',
            'completed'        => 'نتائج جاهزة',
            'cancelled'        => 'ملغى',
            default            => 'غير محدد'
        };
    }
}