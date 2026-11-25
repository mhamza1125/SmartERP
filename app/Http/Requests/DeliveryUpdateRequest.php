<?php

namespace App\Http\Requests;

use App\Models\Delivery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Delivery edit. The stock row, stock_type / stock_status / table_name and delivery_no are
 * not taken from here (DeliveryController::update uses the delivery's own record).
 */
class DeliveryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_date' => 'required',
            'employee_id' => 'required',
            'order_id' => 'nullable',
            'description' => 'nullable',
            'table_name' => 'nullable|in:delivery',
            'stock_type' => 'nullable|in:2',
            'stock_status' => 'nullable|in:3',
            'fshipping' => 'nullable',
            'tshipping' => 'nullable',
            'fport_no' => 'nullable',
            'tport_no' => 'nullable',
            'delivery_method' => 'required',
            'delivery_status' => ['required', Rule::in(array_keys(Delivery::STATUSES))],
            'fi_no' => 'nullable|string|max:255',
            'freight_charges' => 'nullable|numeric|min:0',
            'delivery_date' => 'nullable|date',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|numeric|min:0',
            'batch_qty' => 'nullable|array',
            'batch_qty.*' => 'nullable|array',
            'batch_qty.*.*' => 'nullable|numeric|min:0',
        ];
    }
}
