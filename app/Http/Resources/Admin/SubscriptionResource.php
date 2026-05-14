<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            
            
            'lab_title' => $this->lab->laboratory->name ?? 'مخبر غير مسمى', 
            'owner_name' => $this->lab->name,                    
            'lab_owner_email' => $this->lab->email,
            
            'billing_month' => $this->billing_month,
            'amount' => number_format($this->amount, 2) . ' $',
            'status' => $this->status,
            'paid_at' => $this->paid_at ? $this->paid_at->format('Y-m-d H:i') : 'بانتظار الدفع',
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
