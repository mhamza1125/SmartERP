<?php

namespace App\Http\Requests;

use App\Models\StockBatch;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Split part of one stage of a batch into a new batch, reserved for an order or free.
 */
class StockBatchSplitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit', StockBatch::class);
    }

    public function rules(): array
    {
        return [
            'stage_id' => 'required|integer|min:0',
            'quantity' => 'required|numeric|gt:0',
            'order_id' => 'nullable|integer|exists:orders,order_id',
            'transfer_date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.gt' => 'Enter the quantity to split off.',
        ];
    }
}
