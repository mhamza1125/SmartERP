<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A stock adjustment (StockAdjustmentRepository::store): direction, date, reason, optional order
 * (decrease of reserved stock) and lines of materials or products. Stage / rejected-at choices are
 * checked against the product in the repository.
 */
class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // StockAdjustmentController authorizes through StockAdjustmentPolicy
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => ['required', Rule::in([1, 2])],
            'stock_date' => ['required', 'date'],
            'order_id' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.type' => ['required', Rule::in(['material', 'product'])],
            'lines.*.material_id' => ['required_if:lines.*.type,material', 'nullable', 'integer', 'exists:materials,material_id'],
            'lines.*.product_type_id' => ['required_if:lines.*.type,product', 'nullable', 'integer', 'exists:product_types,product_type_id'],
            'lines.*.stage_id' => ['required_if:lines.*.type,product', 'nullable', 'integer'],
            'lines.*.rejected_stage_id' => ['nullable', 'integer', 'min:0'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999', 'decimal:0,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Enter the reason for the adjustment.',
            'lines.required' => 'Add at least one item.',
            'lines.*.quantity.gt' => 'Every quantity must be greater than 0.',
        ];
    }
}
