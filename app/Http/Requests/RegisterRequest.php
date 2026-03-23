<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Services\ApiResponseService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as IlluminateValidationValidator;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name'=>'string|required|max:100',
            'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')
                ],
            'password'=>['required','min:8','string',
                Password::min(8)->letters()->symbols()->numbers()
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
