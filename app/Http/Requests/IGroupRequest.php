<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IGroupRequest extends FormRequest
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
            'igroup_no' => 'required',
            // 'order_id' => 'required',
            'igroup_date' => 'required',
            'igroup_status' => '',
            'description' => '',
            // Issuance Group Items
            'product_type_id.*' => 'required',
            'material_id.*' => 'required',
            'quantity.*' => 'required',
            'stage_id.*' => '',
        ];
    }
}
