<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class InventoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_manager');
    }

    public function rules(): array
    {
        return [
            'master_item_id'   => 'required|integer|exists:master_items,id',
            'current_quantity' => 'required|integer|min:0', 
            'alert_level'      => 'required|integer|min:1', 
        ];
    }
}