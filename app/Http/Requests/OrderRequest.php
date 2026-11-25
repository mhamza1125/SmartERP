<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            // Typed by the user (empty = SLE-O number); unique (D7)
            'order_no' => 'nullable|string|max:255|unique:orders,order_no',
            'job_no' => 'nullable|max:255',
            'customer_id' => 'required|integer|exists:customers,customer_id',
            'description' => '',
            'order_date' => 'required',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string',
            // A new order starts as Draft or Confirmed (D5; later moves go through the status menu)
            'order_status' => 'required|integer|in:'.implode(',', \App\Models\Order::INITIAL_STATUSES),
            // Purchase Items
            'product_type_id.*' => 'required',
            'product_stage_id.*' => 'required',
            'quantity.*' => 'required|numeric|min:0.01',
            'price.*' => 'required|numeric|min:0',
            // 'price2.*' => 'required',
            // 'head_id.*' => 'required',
            // 'exchange.*' => 'required',
        ];
    }
}
