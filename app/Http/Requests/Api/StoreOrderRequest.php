<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * @property mixed $address_id
 * @property mixed $area_id
 * @property mixed $coupon_id
 * @property mixed $payment_method
 * @property mixed $notes
 * @property mixed $wallet_number
 */
class StoreOrderRequest extends FormRequest
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
            'address_id' => 'required|integer|exists:addresses,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'coupon_id' => 'nullable|integer|exists:coupons,id',
            'payment_method' => 'required|string|in:cash,wallet,visa',
            'notes' => 'nullable|string|max:255',
            'wallet_number' => 'nullable|string|max:255|regex:/^01\d{9}$/',
        ];
    }
}
