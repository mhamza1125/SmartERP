<?php

namespace App\Http\Requests;

use App\Support\Uploads;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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
        $to = (string) $this->input('transaction_to');

        return [
            'transaction_to' => ['required', Rule::in(array_keys(self::TYPES_BY_PARTY))],
            'transaction_type' => ['required', Rule::in(self::TYPES_BY_PARTY[$to] ?? [])],
            'transaction_date' => 'required|date',
            // 0 = cash, otherwise an existing bank account
            'bank_id' => ['required', 'integer', function (string $attribute, $value, \Closure $fail) {
                if ((int) $value !== 0 && ! DB::table('banks')->where('bank_id', (int) $value)->exists()) {
                    $fail('The selected bank account does not exist.');
                }
            }],
            'order_id' => '',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'cc_amount' => 'nullable|numeric|min:0',
            'fb_charges' => 'nullable|numeric|min:0',
            'db_charges' => 'nullable|numeric|min:0',
            // Journal vouchers and expenses post their figure as "amount"
            'amount' => ['nullable', 'numeric', 'gt:0', Rule::requiredIf(in_array($to, ['generalVoucher', 'expense'], true))],
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
            // Same rules storeImage() applies (PDF was accepted here but always refused there, after the save)
            'image.*' => Uploads::imageRules(),
        ];
    }

    /**
     * transaction_to => the transaction_type values its form offers (Pay Customer / Vendor /
     * Contractor / Employee, Expense, Journal Voucher, Balance Adjustment). Opening balances,
     * bank charges, delivery expenses, salaries, transfers, assets and liabilities are created
     * by the application itself, never through this request.
     */
    public const TYPES_BY_PARTY = [
        'customer' => ['orderPayment'],
        'vendor' => ['payment', 'advance', 'receiveAdvance'],
        'contractor' => ['payment', 'advance', 'receiveAdvance', 'wages'],
        'employee' => ['payment', 'advance', 'receiveAdvance', 'wages'],
        'expense' => ['expense'],
        'generalVoucher' => ['generalVoucher'],
        'brs' => ['brs'],
    ];

    /** Every entry must move a positive amount (amount for JV / expense, else debit or credit). */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty() || in_array($this->input('transaction_to'), ['generalVoucher', 'expense'], true)) {
                return;
            }
            if ((float) $this->input('debit') <= 0 && (float) $this->input('credit') <= 0 && (float) $this->input('cc_amount') <= 0) {
                $validator->errors()->add('amount', 'Enter an amount greater than 0.');
            }
        });
    }
}
