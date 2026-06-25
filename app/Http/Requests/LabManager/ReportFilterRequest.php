<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_manager');
    }

    public function rules(): array
    {
        return [
            'period'         => 'nullable|string|in:today,last_7_days,last_30_days,this_year',
            'staff_id'       => 'nullable|integer|exists:users,id',
            'master_test_id' => 'nullable|integer|exists:master_tests,id',
        ];
    }
}