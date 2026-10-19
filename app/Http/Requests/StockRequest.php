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
            // Quantity per batch per product/stage: batch_qty["{product_type_id}_{stage_id}"][batch_id]; empty = auto
            'batch_qty' => 'nullable|array',
            'batch_qty.*' => 'nullable|array',
            'batch_qty.*.*' => 'nullable|numeric|min:0',
            'issue_id' => '',
            'stock_no' => 'required',
            'issue_for' => '',
            'order_id' => 'required',
            'machine_id' => '',
            'employee_id' => 'required',
            'table_name' => 'required',
            'stock_date' => 'required',
            'stock_type' => 'required',
            'stock_status' => 'required',
            'description' => '',
            // Stock Items
            'product_type_id.*' => 'required',
            'material_id.*' => 'required',
            'quantity.*' => 'required',
            'igroup_id.*' => '',
            'stage_id.*' => '',
            'work_logs.*' => '',
        ];
    }
}
