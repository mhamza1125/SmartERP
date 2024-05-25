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
            // Receive Material
            'purchase_item_id.*' => 'required',
            'material_id.*' => 'required',
            'quantity.*' => 'required',
            'stage_id.*' => '',
            'work_logs.*' => '',
        ];
    }
}
