<?php

namespace App\Http\Requests;

use App\Repositories\OrderItemRepository;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'purchase_no' => 'required|max:255',
            'purchase_type' => 'required|max:255',
            'vendor_id' => 'required',
            'order_id' => 'required|integer|min:0',
            'description' => '',
            'purchase_date' => 'required',
            'require_date' => 'required',
            // Purchase Items
            'material_id.*' => 'required',
            'product_type_id.*' => 'required',
            'product_stage_id.*' => 'required',
            'quantity.*' => 'required|numeric|min:0.01',
            'amaterial_id.*' => 'required',
            'aquantity.*' => 'required|numeric|min:0',
            'bmaterial_id.*' => 'required',
            'bquantity.*' => 'required|numeric|min:0',
            'price.*' => 'required|numeric|min:0',
        ];
    }

    /**
     * A purchase against an order may only contain that order's products (product purchase)
     * or the materials of the order's products (material purchase). Quantities are not limited.
     * Order 0 is a general purchase and is not restricted.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $orderId = (int) $this->input('order_id');
            $purchaseType = $this->input('purchase_type');
            // Material processing (mProcess) shares this request and is not restricted
            if ($orderId <= 0 || ! in_array($purchaseType, ['material', 'product'], true)) {
                return;
            }

            $orderItemRepository = app(OrderItemRepository::class);
            if ($purchaseType === 'product') {
                $field = 'product_type_id';
                $allowed = $orderItemRepository->purchasableProductTypeIds($orderId);
                $message = 'Only products of the selected order (or their components) can be purchased against it.';
            } else {
                $field = 'material_id';
                $allowed = $orderItemRepository->purchasableMaterialIds($orderId);
                $message = 'Only materials used by the selected order\'s products can be purchased against it.';
            }

            $invalid = collect((array) $this->input($field, []))
                ->map(fn ($id) => (int) $id)
                ->reject(fn ($id) => $allowed->contains($id));

            if ($invalid->isNotEmpty()) {
                $validator->errors()->add($field, $message);
            }
        });
    }
}
