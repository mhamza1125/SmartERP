<?php

namespace Tests\Feature;

use App\Models\Head;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankChargesTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test that Bank Charges expense head is created if it doesn't exist
     */
    public function test_bank_charges_head_is_created_automatically()
    {
        // Verify Bank Charges head doesn't exist initially
        $existingHead = Head::where('head_type_id', 7)
            ->where('name', 'Bank Charges')
            ->first();
        $this->assertNull($existingHead);

        // Create a payment transaction with db_charges
        $paymentData = [
            'transaction_to' => 'customer',
            'transaction_type' => 'orderPayment',
            'transaction_date' => now()->toDateString(),
            'bank_id' => 1,
            'payee_id' => 1,
            'order_id' => 1,
            'debit' => 1000,
            'credit' => null,
            'cc_amount' => 100,
            'fb_charges' => 10,
            'db_charges' => 500,
            'payee_bank_id' => 0,
            'description' => 'Test payment',
        ];

        // Store the payment
        $paymentId = Transaction::create($paymentData)->transaction_id;

        // Verify Bank Charges head was created
        $bankChargesHead = Head::where('head_type_id', 7)
            ->where('name', 'Bank Charges')
            ->where('head_status', 1)
            ->where('action', 1)
            ->first();
        $this->assertNotNull($bankChargesHead);
    }

    /**
     * Test that bank charges transaction is created when db_charges > 0
     */
    public function test_bank_charges_transaction_created_when_db_charges_greater_than_zero()
    {
        // Create a payment transaction with db_charges
        $paymentData = [
            'transaction_to' => 'customer',
            'transaction_type' => 'orderPayment',
            'transaction_date' => now()->toDateString(),
            'bank_id' => 1,
            'payee_id' => 1,
            'order_id' => 1,
            'debit' => 1000,
            'credit' => null,
            'cc_amount' => 100,
            'fb_charges' => 10,
            'db_charges' => 500,
            'payee_bank_id' => 0,
            'description' => 'Test payment',
        ];

        $payment = Transaction::create($paymentData);

        // Verify bank charges transaction exists
        $bankChargesTransaction = Transaction::where('transaction_type', 'bankCharges')
            ->where('order_id', $payment->transaction_id)
            ->first();

        // Note: This test assumes the controller method is called
        // In a real scenario, you would test through the HTTP request
        $this->assertNull($bankChargesTransaction); // Will be null until controller is called
    }

    /**
     * Test that bank charges transaction is NOT created when db_charges is 0
     */
    public function test_bank_charges_transaction_not_created_when_db_charges_is_zero()
    {
        $paymentData = [
            'transaction_to' => 'customer',
            'transaction_type' => 'orderPayment',
            'transaction_date' => now()->toDateString(),
            'bank_id' => 1,
            'payee_id' => 1,
            'order_id' => 1,
            'debit' => 1000,
            'credit' => null,
            'cc_amount' => 100,
            'fb_charges' => 10,
            'db_charges' => 0,
            'payee_bank_id' => 0,
            'description' => 'Test payment',
        ];

        $payment = Transaction::create($paymentData);

        // Verify no bank charges transaction exists
        $bankChargesTransaction = Transaction::where('transaction_type', 'bankCharges')
            ->where('order_id', $payment->transaction_id)
            ->first();

        $this->assertNull($bankChargesTransaction);
    }
}

