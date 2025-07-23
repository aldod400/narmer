<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * @property mixed $per_page
 * @property mixed $status
 * @property mixed $payment_status
 * @property mixed $payment_method
 * @property mixed $search
 */
class FilterOrderRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:ready,on_delivery,completed',
            'payment_status' => 'nullable|string|in:unpaid,paid',
            'payment_method' => 'nullable|string|in:cash,visa,wallet',
            'search' => 'nullable|string'
        ];
    }
}
