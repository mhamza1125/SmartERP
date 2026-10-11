<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PtcReceiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Header fields of a PTC receiving. Item lines are validated by PtcStockRepository.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'issue_id' => 'nullable|integer',
            'stock_date' => 'required|date',
            'employee_id' => 'required|integer|min:1',
            'table_name' => 'nullable|in:employee,vendor',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_date.required' => 'Select the receiving date.',
            'employee_id.required' => 'Select the employee / contractor received from.',
            'employee_id.min' => 'Select the employee / contractor received from.',
        ];
    }
}
