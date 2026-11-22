<?php

namespace App\Http\Requests;

use App\Models\PartyPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A vendor / customer price of one item: a material (vendors only) or a product size + stage.
 * Used by partyPrice.store (price required) and partyPrice.lookup (no price).
 */
class PartyPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // PartyPriceController authorizes through the Purchase / Order policies
    }

    public function rules(): array
    {
        $vendor = $this->input('party_type') === PartyPrice::PARTY_VENDOR;

        return [
            'party_type' => ['required', Rule::in([PartyPrice::PARTY_VENDOR, PartyPrice::PARTY_CUSTOMER])],
            'party_id' => ['required', 'integer', $vendor ? 'exists:vendors,vendor_id' : 'exists:customers,customer_id'],
            'material_id' => ['nullable', 'integer', $vendor ? 'exists:materials,material_id' : 'prohibited'],
            'product_type_id' => ['nullable', 'integer', 'exists:product_types,product_type_id'],
            'stage_id' => ['nullable', 'integer', 'exists:heads,head_id'],
            'price' => $this->routeIs('partyPrice.store')
                ? ['required', 'numeric', 'gt:0', 'max:9999999999', 'decimal:0,3']
                : ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $material = (int) $this->input('material_id');
            $product = (int) $this->input('product_type_id');
            if (($material > 0) === ($product > 0)) {
                $v->errors()->add('material_id', 'Select either a material or a product size.');
            }
            if ($product > 0 && ! (int) $this->input('stage_id') && $this->routeIs('partyPrice.store')) {
                $v->errors()->add('stage_id', 'Select the product stage.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'party_id.required' => 'Select the vendor / customer.',
            'price.gt' => 'The price must be greater than 0.',
        ];
    }
}
