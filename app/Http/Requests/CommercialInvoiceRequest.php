<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Options entered in the Commercial Invoice modal on the delivery page (query string).
 * Every field is optional; an empty payment_percent means 100%.
 */
class CommercialInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hs_code' => 'nullable|string|max:255',
            'selling_type' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:255',
            'bank_id' => 'nullable|integer',
            'statement_of_origin' => 'nullable|string|max:5000',
            // Overrides the delivery's FI No and the default FI No in Settings > Company
            'fi_no' => 'nullable|string|max:255',
            // Share of the invoice value requested now (e.g. 50 when 50% was paid in advance)
            'payment_percent' => 'nullable|numeric|gt:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_percent.gt' => 'Payment percentage must be more than 0%.',
            'payment_percent.max' => 'Payment percentage cannot be more than 100%.',
        ];
    }
}
