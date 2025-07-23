<?php

namespace App\Http\Requests\Api\Auth;

use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property UploadedFile $image
 * @property string $fcm_token
 * @property string $password
 */
class UpdadeProfileRequest extends FormRequest
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
            'email' => 'required_without:phone|string|email:dns|max:255|unique:users,email,' . auth('api')->id(),
            'phone' => 'required_without:email|string|regex:/^01\d{9}$/|unique:users,phone,' . auth('api')->id(),
            'image' => 'nullable|image|max:2048',
            'fcm_token' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed|regex:/[A-Za-z]/|regex:/[0-9]/',
        ];
    }
    public function messages()
    {
        return [
            'password.regex' => __('message.The password must contain at least one letter and one number.'),
            'phone.regex' => __('message.The phone must be a valid Egyptian phone number.'),
        ];
    }
}
