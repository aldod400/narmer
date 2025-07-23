<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Response;

class PaymobCallBackRequest extends FormRequest
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
            'obj.id' => 'required|integer',
            'obj.data.txn_response_code' => 'nullable|string',
            'obj.data.message' => 'required|string',
            'obj.pending' => 'required|boolean',
            'obj.success' => 'required|boolean',
            'obj.source_data.type' => 'required|string',
            'obj.source_data.sub_type' => 'required|string',
            'obj.order.shipping_data.order_id' => 'required|integer',
            'hmac' => 'required|string',
        ];
    }
}
