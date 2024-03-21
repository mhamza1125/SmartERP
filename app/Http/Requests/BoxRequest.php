<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BoxRequest extends FormRequest
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
            'box_no' => 'required|max:255',
            'name' => 'required|max:255',
            'length' => 'required',
            'width' => 'required',
            'height' => 'required',
            'weight' => 'required',
            'box_status' => 'required',
            'head_id' => 'required',
            'description' => '',
        ];
    }
}
