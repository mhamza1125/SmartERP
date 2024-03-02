<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
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
            'stock_no' => 'required',
            'order_id' => 'required',
            'employee_id' => 'required',
            'stock_date' => 'required',
            'department_id' => '',
            'stock_type' => '',
            'description' => '',
            // Receive Material
            'purchase_item_id.*' => 'required',
            'material_id.*' => 'required',
            'quantity.*' => 'required',
            'product_stage.*' => '',
        ];
    }
}
