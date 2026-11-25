<?php

namespace App\Http\Requests;

use App\Support\Uploads;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Edit of an issuance, machine-material issue or issuance receipt (stock.update).
 * The control columns are not taken from here: StockRepository::issuanceHeader() sets them
 * from the stored record. The posted values are only checked against the allowed lists.
 */
class StockUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_date' => 'nullable|date',
            'table_name' => 'nullable|in:employee,vendor',
            'stock_type' => 'nullable|in:1,2',
            'stock_status' => 'nullable|in:0,1,2,4',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|numeric|min:0',
            'batch_qty' => 'nullable|array',
            'batch_qty.*' => 'nullable|array',
            'batch_qty.*.*' => 'nullable|numeric|min:0',
            'image' => 'nullable|array',
            'image.*' => Uploads::imageRules(),
        ];
    }
}
