<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class SearchTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // الحقل مطلوب، نصي، ولا يقل عن حرفين لضمان دقة البحث
            'query' => 'required|string|min:2|max:100', 
        ];
    }
}