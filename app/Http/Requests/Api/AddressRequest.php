<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

/**
 * @property string $name 
 * @property string $address
 * @property string $phone 
 * @property float $lat
 * @property float $lng
 * @property bool $is_default
 * @property int $city_id
 */
class AddressRequest extends FormRequest
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
            'name' => 'required|string|in:home,work,other',
            'address' => 'required|string',
            'phone' => 'required|string|regex:/^01\d{9}$/',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'is_default' => 'required|boolean',
            'city_id' => 'required|exists:cities,id'
        ];
    }
}
