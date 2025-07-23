<?php

namespace App\Http\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * @property int $product_id
 * @property int $quantity
 * @property array $product_attribute_value_ids
 */
class StoreCartRequest extends FormRequest
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
            'product_id'                    => 'required|integer|exists:products,id',
            'quantity'                      => 'required|integer|min:1',
            'product_attribute_value_ids'   => 'nullable|array',
            'attribute_value_ids.*'         => 'integer|exists:product_attribute_values,id',
        ];
    }
}
