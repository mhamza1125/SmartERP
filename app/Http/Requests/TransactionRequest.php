<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'cc_amount' => 'nullable|numeric|min:0',
            'fb_charges' => 'nullable|numeric|min:0',
            'db_charges' => 'nullable|numeric|min:0',
            'amount' => '',
            'fees_expenses' => 'nullable|numeric|min:0',
            'payee_id' => '',
            'payee_bank_id' => '',
            // Optional Expense Head on a Journal Voucher (heads of type 7 = Expenses)
            'expense_head_id' => ['nullable', 'integer', Rule::exists('heads', 'head_id')->where('head_type_id', 7)],
            'payee_type' => '',
            'voucher_type' => '',
            'description' => '',
            'advance_type' => 'nullable|in:temporary,fixed',
            'image' => 'nullable|array',
            'image.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        ];
    }
}
