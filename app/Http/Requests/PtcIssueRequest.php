<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PtcIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Header fields of a PTC issuance / stage transfer (create and edit).
     * Item lines are validated by PtcStockRepository.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stock_date' => 'required|date',
            'issue_for' => 'required|integer|min:1',
            'employee_id' => 'required|integer|min:1',
            'table_name' => 'nullable|in:employee,vendor',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_date.required' => 'Select the issuance date.',
            'issue_for.required' => 'Select the stage to issue for.',
            'issue_for.min' => 'Select the stage to issue for.',
            'employee_id.required' => 'Select the employee / contractor to issue to.',
            'employee_id.min' => 'Select the employee / contractor to issue to.',
        ];
    }
}
