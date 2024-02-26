<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'purchase_no' => 'required|max:255',
            'vendor_id' => 'required',
            'description' => '',
            'purchase_date' => 'required',
            'require_date' => 'required',
            // Purchase Items
            'material_id.*' => 'required',
            'quantity.*' => 'required',
            'price.*' => 'required',
        ];
    }
}
