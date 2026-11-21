<?php

namespace App\Models;

/**
 * One entry of the liability register (table `assets`, kind 'liability'): loans taken and
 * other amounts the company owes. Outstanding = SUM(debit) - SUM(credit) (debit = increase,
 * credit = decrease), same columns and money link as Asset.
 */
class Liability extends Asset
{
    public const KIND = 'liability';

    /** Same shape as Asset::TYPES. */
    public const TYPES = [
        'incurred' => ['label' => 'New Liability / Loan Received', 'short' => 'New Liability', 'sign' => 1, 'money' => 'in'],
        'charge' => ['label' => 'Interest / Charges Added', 'short' => 'Charges', 'sign' => 1, 'money' => null],
        'payment' => ['label' => 'Payment / Repayment', 'short' => 'Payment', 'sign' => -1, 'money' => 'out'],
        'waiver' => ['label' => 'Waived / Written Off', 'short' => 'Waived', 'sign' => -1, 'money' => null],
        'adjustment' => ['label' => 'Adjustment (Increase)', 'short' => 'Adjustment +', 'sign' => 1, 'money' => null],
        'adjustment_decrease' => ['label' => 'Adjustment (Decrease)', 'short' => 'Adjustment -', 'sign' => -1, 'money' => null],
    ];
}
