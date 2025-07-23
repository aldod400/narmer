<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $image
 * @property string $fcm_token
 * @property string $user_type
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            Response::api($validator->errors()->first(), 400, false, 400)
        );
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required_without:phone|string|email:dns|max:255|unique:users,email',
            'phone' => 'required_without:email|string|unique:users,phone|regex:/^01\d{9}$/',
            'password' => 'required|string|min:8|confirmed | regex:/[A-Za-z]/ | regex:/[0-9]/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'fcm_token' => 'nullable|string',
            'user_type' => 'required|string|in:user,deliveryman'
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => __('message.The phone must be a valid Egyptian phone number.'),
            'password.regex' => __('message.The password must contain at least one letter and one number.'),
        ];
    }
}
