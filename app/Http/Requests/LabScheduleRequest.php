<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('lab_manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        return [
            'is_day_off' => 'required|boolean',
            'start_time' => 'required_if:is_day_off,false|nullable|date_format:H:i',
            'end_time'   => 'required_if:is_day_off,false|nullable|date_format:H:i|after:start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => 'وقت الانتهاء يجب أن يكون بعد وقت البداية',
        ];
    }
}
