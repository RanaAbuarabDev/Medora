<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Services\ApiResponseService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as IlluminateValidationValidator;

class LoginRequest extends FormRequest
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
            'email'=>'required|email|max:60',
            'password'=>['required','min:10','string',
                Password::min(10)->letters()->numbers()
             ],
        ];
    }


    protected function failedValidation(IlluminateValidationValidator $validator)
    {
        $errors = $validator->errors()->all();

        $response = ApiResponseService::error(
            $errors,
            "Validation Errors",
            422
        );

        throw new HttpResponseException($response);
    }
}
