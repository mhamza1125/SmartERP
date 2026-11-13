<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Options of the proforma invoice modal (print and Excel export), all optional.
 * Authorization is checked in the controller (Order policy).
 */
class ProformaInvoiceRequest extends FormRequest
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
        ];
    }
}
