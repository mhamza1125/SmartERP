<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'ceo' => 'required|string|max:255',
            'ntn' => 'nullable|string|max:255',
            'rex_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_path' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'statement_of_origin' => 'nullable|string',
        ];
    }
}
