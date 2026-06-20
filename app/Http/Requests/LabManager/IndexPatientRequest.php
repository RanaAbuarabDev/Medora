<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class IndexPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:all,male,female,ذكر,أنثى',
            'page'   => 'nullable|integer|min:1',
        ];
    }
}