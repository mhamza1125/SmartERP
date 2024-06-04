<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:255|string',
            'email' => 'required|max:255|email',
            'role' => 'required|string|max:255',
        ];

        // If it's a store request, add password validation
        if ($this->isMethod('post')) {
            $rules['password'] = 'required|confirmed';
        }

        // If it's an update request, add email uniqueness validation
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['email'] .= '|unique:users,email,' . $this->route('id');
            // Add conditional password validation
            $rules['password'] = 'sometimes|required|confirmed';
        }

        return $rules;
    }
}
