<?php

namespace App\Http\Requests\LabAssistant;

use Illuminate\Foundation\Http\FormRequest;

class ConsumeSuppliesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_assistant'); // مخصص للمساعد المخبري فقط
    }

    public function rules(): array
    {
        return [
            'consumables' => 'required|array|min:1',
            'consumables.*.lab_inventory_id' => 'required|integer|exists:lab_inventories,id',
            'consumables.*.quantity_used' => 'required|integer|min:1'
        ];
    }
}