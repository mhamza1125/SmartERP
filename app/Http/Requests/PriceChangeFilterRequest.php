<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filters of the Price Change History page (partyPrice.changes): product / material, vendor or
 * customer, and the user who made the change. Authorization is checked in PartyPriceController.
 */
class PriceChangeFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item' => ['nullable', 'string', 'regex:/^(material|product):\d+$/'],
            'party_id' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'dfrom' => ['nullable', 'date_format:Y-m-d'],
            'dto' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dfrom'],
        ];
    }

    public function attributes(): array
    {
        return ['dfrom' => 'Date From', 'dto' => 'Date To', 'party_id' => 'Vendor / Customer', 'user_id' => 'User'];
    }
}
