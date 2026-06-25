<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_manager');
    }

    public function rules(): array
    {
        return [
            'date'       => 'nullable|date_format:Y-m-d',
            'status'     => 'nullable|string|in:paid,pending,failed', 
            'staff_id'   => 'nullable|integer|exists:users,id', 
            'search'     => 'nullable|string|max:100', 
            'per_page'   => 'nullable|integer|in:5,10,15,20',
        ];
    }
}