<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryReturnRequest extends FormRequest
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
            'return_no' => 'required|string|max:255',
            'delivery_id' => 'required|integer|exists:deliveries,delivery_id',
            'return_date' => 'required|date',
            'return_reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock_item_id.*' => 'required|integer|exists:stock_items,stock_item_id',
            'return_quantity.*' => 'required|numeric|min:0',
            'reason.*' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'return_no.required' => 'Return number is required.',
            'delivery_id.required' => 'Delivery ID is required.',
            'delivery_id.exists' => 'Selected delivery does not exist.',
            'return_date.required' => 'Return date is required.',
            'return_reason.required' => 'Return reason is required.',
            'stock_item_id.*.required' => 'Stock item is required.',
            'stock_item_id.*.exists' => 'Selected stock item does not exist.',
            'return_quantity.*.required' => 'Return quantity is required.',
            'return_quantity.*.min' => 'Return quantity must be greater than or equal to 0.',
        ];
    }
}
