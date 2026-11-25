<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Order edit. Only these fields are taken from the form: order_status (status menu),
 * order_no / job_no (generated), created_by and line totals (price x quantity) are set by the server.
 */
class OrderUpdateRequest extends FormRequest
{
    /** Order header columns an edit may change. */
    public const HEADER_FIELDS = ['customer_id', 'order_date', 'due_date', 'payment_terms', 'expected_delivery_date', 'description'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,customer_id',
            'order_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string',
            'expected_delivery_date' => 'nullable|date',
            'description' => 'nullable|string',
            'product_type_id' => 'required|array|min:1',
            'product_type_id.*' => 'required|integer',
            'product_stage_id' => 'required|array',
            'product_stage_id.*' => 'required|integer',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array',
            'price.*' => 'required|numeric|min:0',
        ];
    }
}
