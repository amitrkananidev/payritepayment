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
        'distributor-fund-load/phonepe/callback',
        'phonepe_callback_distributor',
        'airpay/response',
        'response_airpay',
        'airpay/payment',
        'airpay_payment_retailer'
    ];
}
