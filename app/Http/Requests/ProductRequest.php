<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'category_id' => 'required',
            'article_no' => 'required|max:255',
            'hs_code' => 'nullable|max:255',
             'name' => 'required|max:255',
            'unit_id' => 'required',
            'product_status' => 'required',
            'opening_stock_size_id.*' => 'nullable|exists:heads,head_id',
            'opening_stock_stage_id.*' => 'nullable|exists:heads,head_id',
            'opening_stock_quantity.*' => 'nullable|numeric|min:0',
            'description' => '',
        ];
    }
}
