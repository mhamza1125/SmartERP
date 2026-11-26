<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The multi-order delivery AJAX route (ajax.customer-orders) must stay closed to guests.
 * DeliveryController applies the 'auth' middleware to getCustomerOrders itself, so the
 * ['web', 'auth'] route group around it is redundant (cleanup D-019, 2026-11-26).
 */
class CustomerOrdersGuestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_to_login_for_customer_orders(): void
    {
        $this->get(route('ajax.customer-orders', 1))->assertRedirect(route('login'));
    }
}
