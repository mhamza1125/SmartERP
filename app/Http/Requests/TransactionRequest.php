<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'transaction_to' => 'required|max:255',
            'transaction_type' => 'required|max:255',
            'transaction_date' => 'required',
            'bank_id' => 'required',
            'order_id' => '',
            'debit' => '',
            'credit' => '',
            'gross_amount' => 'nullable|numeric|min:0',
            'fees_expenses' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric|min:0',
            'payee_id' => '',
            'payee_bank_id' => '',
            'description' => '',
        ];
    }
}
