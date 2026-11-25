<?php

namespace App\Http\Requests;

use App\Support\Uploads;
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
            // Server decides stock_type / stock_status / table_name (StockRepository::issuanceHeader);
            // the posted values are only checked against the allowed lists.
            'issue_id' => 'nullable|integer',
            'stock_no' => 'required',
            'issue_for' => '',
            'order_id' => 'required',
            'machine_id' => '',
            'employee_id' => 'required',
            'table_name' => 'required|in:employee,vendor',
            'stock_date' => 'required',
            'stock_type' => 'nullable|in:1,2',
            'stock_status' => 'nullable|in:0,1,2,4',
            'description' => '',
            // Stock Items
            'product_type_id.*' => 'required',
            'material_id.*' => 'required',
            'quantity.*' => 'required',
            'igroup_id.*' => '',
            'stage_id.*' => '',
            'work_logs.*' => '',
            'image' => 'nullable|array',
            'image.*' => Uploads::imageRules(),
        ];
    }
}
