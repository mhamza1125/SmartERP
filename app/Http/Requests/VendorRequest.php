<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRequest extends FormRequest
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
            'vendor_type_id' => 'required',
            'city_id' => 'required',
            'name' => 'required|max:255',
            'fname' => 'required|max:255',
            'phone1' => 'required|max:255',
            'phone2' => '',
            'address' => 'required|max:255',
            'description' => '',
        ];
    }
}
