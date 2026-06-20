<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class GetAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:all,pending,processing,completed,cancelled',
            'date_filter' => 'nullable|in:all,today,weekly',
        ];
    }
}