<?php

namespace App\Http\Requests\LabAssistant;

use Illuminate\Foundation\Http\FormRequest;

class FilterLabResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تفعيل الصلاحية
    }

    public function rules(): bool|array
    {
        return [
            'search'     => 'nullable|string|max:100',
            'date_range' => 'nullable|string|in:today,last_7_days,last_30_days,all',
            'status'     => 'nullable|string|in:draft,completed,all',
        ];
    }
}