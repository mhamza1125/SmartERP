<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryRequest extends FormRequest
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
            // Stock Table
            // Note: stock_no is auto-generated server-side, not provided by user
            // Quantity per batch per product/stage: batch_qty["{product_type_id}_{stage_id}"][batch_id]; empty = auto
            'batch_qty' => 'nullable|array',
            'batch_qty.*' => 'nullable|array',
            'batch_qty.*.*' => 'nullable|numeric|min:0',
            'stock_date' => 'required', // Delivery Date
            'order_id' => 'required_without:order_ids', // Required for single order delivery
            'order_ids' => 'required_without:order_id|string', // Required for multi-order delivery (comma-separated)
            'selected_orders' => 'nullable|array',
            'selected_orders.*' => 'nullable|exists:orders,order_id',
            'employee_id' => 'required',
            // Control columns are set by the server (Delivery::STOCK_HEADER); posted values only checked
            'table_name' => 'nullable|in:delivery',
            'stock_type' => 'nullable|in:2',
            'stock_status' => 'nullable|in:3',
            'description' => '',
            // Delivery Table
            'tshipping' => '',
            'fshipping' => '',
            'tport_no' => '',
            'fport_no' => '',
            'delivery_method' => 'required',
            'delivery_status' => 'required|in:1,2,3,4',
            'fi_no' => 'nullable|string|max:255',
            'freight_charges' => 'nullable|numeric|min:0',
            'delivery_no' => 'nullable|string|max:255|unique:deliveries,delivery_no', // empty = SLE-D number (D7: unique)
            'delivery_date' => 'nullable|date',
            // Tranaction Table
            'payee_id.*' => '',
            'bank_id.*' => '',
            'debit.*' => '',
            'remarks.*' => '',
            // Stock Items Table
            'product_type_id.*' => 'required',
            'material_id.*' => 'required',
            'quantity.*' => 'required|numeric|min:0',
            'stage_id.*' => 'required',
            // Delivery Boxes Table
            'vehicle_no.*' => '',
            'rowQty.*' => '',
            'totalQty.*' => '',
        ];
    }
}
