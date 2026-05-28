<?php

namespace App\Http\Resources\Reception;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentManagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'invoice_number'   => $this->invoice ? '#INV-' . $this->invoice->id : 'N/A',
            'patient_name'     => $this->patient->name ?? 'مريض غير معروف',
            'patient_initials' => $this->patient ? mb_substr($this->patient->name, 0, 2) : '??', // الحرفين داخل الدائرة الرمادية
            'appointment_time' => \Carbon\Carbon::parse($this->start_time)->format('H:i'),
            'time_period'      => \Carbon\Carbon::parse($this->start_time)->format('A') === 'AM' ? 'ص' : 'م',
            'test_tags'        => $this->labTests->pluck('name'), // مصفوفة بأسماء التحاليل لتظهر كـ Badges (فحص دوري، هرمونات)
            'total_amount'     => $this->invoice->total_amount ?? 0,
            'status'           => $this->status, // مكتمل، قيد التحليل، ملغى
        ];
    }
}