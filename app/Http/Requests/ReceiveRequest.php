<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * New purchase receipt. Each line: received quantity = approved + rejected + pending
 * (approved is what enters stock). Lines must belong to the receipt's purchase.
 * Over-receipt and lock rules are checked in ReceiveRepository::saveReceipt().
 */
class ReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'receive_no' => 'required|string|max:255|unique:receives,receive_no',
            'purchase_id' => 'required|integer|exists:purchases,purchase_id',
            'receive_date' => 'required|date',
            'description' => 'nullable|string',
        ], $this->lineRules((int) $this->input('purchase_id')));
    }

    /** The receipt's purchase (the posted one for a new receipt). */
    protected function lineRules(int $purchaseId): array
    {
        return [
            'purchase_item_id' => 'required|array|min:1',
            'purchase_item_id.*' => ['required', 'integer', 'distinct', Rule::exists('purchase_items', 'purchase_item_id')->where('purchase_id', $purchaseId)],
            'inspection_date' => 'required|array',
            'inspection_date.*' => 'required|date',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0',
            'approved_qty' => 'required|array',
            'approved_qty.*' => 'required|numeric|min:0',
            'rejected_qty' => 'required|array',
            'rejected_qty.*' => 'required|numeric|min:0',
            'pending_qty' => 'required|array',
            'pending_qty.*' => 'required|numeric|min:0',
        ];
    }

    /** approved + rejected + pending = received, per line. */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            foreach ((array) $this->input('quantity', []) as $key => $quantity) {
                $split = (float) ($this->input("approved_qty.$key") ?? 0) + (float) ($this->input("rejected_qty.$key") ?? 0) + (float) ($this->input("pending_qty.$key") ?? 0);
                if (abs($split - (float) $quantity) > 0.0001) {
                    $validator->errors()->add("approved_qty.$key", 'Approved + rejected + pending must equal the received quantity.');
                }
            }
        });
    }
}
