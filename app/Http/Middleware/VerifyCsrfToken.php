<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'ajaxPM',
        'ajaxPT',
        'ajaxPTStock',
        'ajaxPC',
        'ajaxPS',
        'ajaxIG',
        'ajaxMQty',
        'ajaxAMQty',
        'ajaxATMQty',
        'ajaxPMQty',
        'ajax/customer-orders/*',
    ];
}
