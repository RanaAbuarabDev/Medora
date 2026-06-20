<?php

namespace App\Http\Requests\LabManager;

use Illuminate\Foundation\Http\FormRequest;

class IndexResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ⚡ تأكدي من تغييرها إلى true
    }

    public function rules(): array
    {
        return [
            'search'      => 'nullable|string|max:255',
            'status'      => 'nullable|string|in:all,pending,completed,in_progress,cancelled',
            'date_filter' => 'nullable|string|in:all,today,weekly',
            'page'        => 'nullable|integer|min:1',
        ];
    }
}