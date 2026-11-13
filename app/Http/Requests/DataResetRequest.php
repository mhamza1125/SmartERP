<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TEMPORARY: confirmation of Settings > Reset Test Data (see App\Support\TransactionalDataReset).
 */
class DataResetRequest extends FormRequest
{
    public const PHRASE = 'RESET';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'understand' => 'accepted',
            'backup_taken' => 'accepted',
            'confirm_phrase' => 'required|in:'.self::PHRASE,
            'reset_opening_balances' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'understand.accepted' => 'Tick the box confirming you understand the data will be permanently deleted.',
            'backup_taken.accepted' => 'Tick the box confirming a database backup has been taken.',
            'confirm_phrase.in' => 'Type '.self::PHRASE.' (capital letters) to confirm.',
        ];
    }
}
