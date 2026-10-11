<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStatusRequest extends FormRequest
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
            'order_status' => ['required', 'integer', Rule::in(array_keys(Order::STATUS_LABELS))],
        ];
    }

    public function messages(): array
    {
        return [
            'order_status.required' => 'Select an order status.',
            'order_status.in' => 'The selected order status is invalid.',
        ];
    }
}
