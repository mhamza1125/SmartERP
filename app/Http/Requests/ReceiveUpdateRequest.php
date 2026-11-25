<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\DB;

/**
 * Edit of a purchase receipt: only the date, description and lines change; the receipt stays on
 * its purchase (receive_no, purchase_id, receive_status and created_by are not taken from here).
 */
class ReceiveUpdateRequest extends ReceiveRequest
{
    public function rules(): array
    {
        $purchaseId = (int) DB::table('receives')->where('receive_id', (int) $this->route('id'))->value('purchase_id');

        return array_merge([
            'receive_date' => 'required|date',
            'description' => 'nullable|string',
        ], $this->lineRules($purchaseId));
    }
}
