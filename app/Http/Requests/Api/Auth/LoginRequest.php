<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * @property string $identifier
 * @property string $password
 * @property string $fcm_token
 */
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
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
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
            'identifier' => [
                'required',
                function ($attribute, $value, $fail) {
                    $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
                    $isPhone = preg_match('/^01\d{9}$/', $value);

                    if ($isEmail) {
                        $domain = explode('@', $value)[1] ?? '';
                        if (!checkdnsrr($domain, 'MX')) {
                            $fail(__('message.The email domain does not exist.'));
                        }
                    } elseif (!$isPhone) {
                        $fail(__('message.The phoneOrEmail must be a valid email or an Egyptian phone number.'));
                    }
                },
                function ($attribute, $value, $fail) {
                    $emailExists = \App\Models\User::where('email', $value)->exists();
                    $phoneExists = \App\Models\User::where('phone', $value)->exists();

                    if (!$emailExists && !$phoneExists) {
                        $fail(__('message.The phoneOrEmail does not exist in our records.'));
                    }
                }
            ],
            'password'   => 'required | min:8',
            'fcm_token' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'identifier.exists'   => __('message.email_not_found'),
            'identifier.regex'    => __('message.The phone number must start with 01 and contain exactly 11 digits'),
            'password.regex' => __('message.The password must contain at least one letter and at least one number'),
        ];
    }
}
