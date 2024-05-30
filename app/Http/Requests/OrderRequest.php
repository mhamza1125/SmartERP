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
            'order_no' => 'required|max:255',
            'job_no' => 'required|max:255',
            'customer_id' => 'required',
            'description' => '',
            'order_date' => 'required',
            'order_status' => 'required',
            // Purchase Items
            'product_type_id.*' => 'required',
            'product_stage_id.*' => 'required',
            'quantity.*' => 'required',
            'price.*' => 'required',
            // 'price2.*' => 'required',
            // 'head_id.*' => 'required',
            // 'exchange.*' => 'required',
        ];
    }
}
