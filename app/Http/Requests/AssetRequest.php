<?php

namespace App\Http\Requests;

use App\Models\Asset;
use App\Models\Liability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * An asset or liability entry (the route name decides which register: asset.* / liability.*).
 * affects_money + bank_id (0 = cash, else a company bank account) only for types that can move
 * money (TYPES[...]['money'] not null).
 */
class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // AssetController / LiabilityController authorize through the policies
    }

    /** @return class-string<Asset> */
    public function modelClass(): string
    {
        return str_starts_with((string) $this->route()?->getName(), 'liability.') ? Liability::class : Asset::class;
    }

    public function rules(): array
    {
        $model = $this->modelClass();

        return [
            'asset_name' => 'required|string|max:255',
            'transaction_type' => ['required', Rule::in(array_keys($model::TYPES))],
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|gt:0|max:9999999999999|decimal:0,2',
            'description' => 'nullable|string|max:2000',
            'affects_money' => 'nullable|boolean',
            'bank_id' => [
                'nullable', 'required_if:affects_money,1', 'integer',
                fn ($attribute, $value, $fail) => (int) $value === 0
                    || \App\Models\Bank::where('bank_id', $value)->where('bank_holder', 'admin')->exists()
                    || $fail('Select Cash or one of the company bank accounts.'),
            ],
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->modelClass()::TYPES[$this->input('transaction_type')] ?? null;
            if ($type && $this->boolean('affects_money') && $type['money'] === null) {
                $validator->errors()->add('affects_money', $type['label'].' does not move money; untick "Affects cash / bank".');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'asset_name' => 'name',
            'transaction_type' => 'entry type',
            'transaction_date' => 'date',
            'bank_id' => 'cash / bank account',
        ];
    }
}
