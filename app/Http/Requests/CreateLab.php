<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLab extends FormRequest
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
            'lab_name' => 'required|string|max:255|unique:laboratories,name',
            'address'  => 'required|string|max:500',
            'phone'    => 'required|string|max:20', 
            'logo'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 

            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

        ];
    }
}
