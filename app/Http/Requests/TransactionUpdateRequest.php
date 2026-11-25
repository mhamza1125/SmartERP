<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Edit of a payment / expense / journal voucher / balance adjustment: the same rules as a new
 * entry, plus the entry's own stored party / type (e.g. a bank opening balance edited on the
 * BRS page keeps transaction_to "admin"). ledger_flag, transfer_pair_id and created_by are
 * never taken from the form (TransactionController::update).
 */
class TransactionUpdateRequest extends TransactionRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $stored = DB::table('transactions')->where('transaction_id', (int) $this->route('id'))->first(['transaction_to', 'transaction_type']);
        if (! $stored) {
            return $rules;
        }
        $to = (string) $this->input('transaction_to');
        $parties = array_keys(self::TYPES_BY_PARTY);
        $types = self::TYPES_BY_PARTY[$to] ?? [];
        if (! in_array($stored->transaction_to, $parties, true)) {
            $parties[] = $stored->transaction_to;
        }
        if ($to === $stored->transaction_to && ! in_array($stored->transaction_type, $types, true)) {
            $types[] = $stored->transaction_type;
        }
        $rules['transaction_to'] = ['required', Rule::in($parties)];
        $rules['transaction_type'] = ['required', Rule::in($types)];

        return $rules;
    }
}
