<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveRequest extends FormRequest
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
            'receive_no' => 'required',
            'purchase_id' => 'required',
            'receive_date' => 'required',
            'description' => '',
            // Receive Material
            'purchase_item_id.*' => 'required',
            'quantity.*' => 'required',
        ];
    }
}
