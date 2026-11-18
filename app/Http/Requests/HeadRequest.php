<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeadRequest extends FormRequest
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
            'head_type_id' => 'required',
            'name' => 'required|max:255',
            // Department heads: working hours (late time / overtime of the department's employees)
            'work_start_time' => 'nullable|date_format:H:i,H:i:s',
            'work_end_time' => 'nullable|date_format:H:i,H:i:s',
        ];
    }
}
