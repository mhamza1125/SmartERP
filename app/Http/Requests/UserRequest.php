<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $id = $this->route('id'); // set when updating

        return [
            'name' => 'required|max:255|string',
            'email' => ['required', 'max:255', 'email', Rule::unique('users', 'email')->ignore($id)],
            'role_id' => 'required|integer|exists:roles,id',
            // New users need a password; on update it is changed only when filled in
            'password' => $id ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
        ];
    }
}
