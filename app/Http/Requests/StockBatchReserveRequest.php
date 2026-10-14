<?php

namespace App\Http\Requests;

use App\Models\StockBatch;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Reserve a whole batch for an order, move it to another order, or free it (empty order).
 */
class StockBatchReserveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit', StockBatch::class);
    }

    public function rules(): array
    {
        return [
            'order_id' => 'nullable|integer|exists:orders,order_id',
            'note' => 'nullable|string|max:500',
        ];
    }
}
