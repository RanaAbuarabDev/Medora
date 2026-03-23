<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMasterTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'test_category_id' => 'nullable|exists:test_categories,id',
            'name'             => 'nullable|string|max:255|unique:master_tests,name',
            'short_name'       => 'nullable|string|max:50',
            'sample_type'      => 'nullable|string|max:100',
            'unit'             => 'nullable|string|max:50',
            'normal_range'     => 'nullable|string',
            'description'      => 'nullable|string|max:1000',
        ];
    }
}
