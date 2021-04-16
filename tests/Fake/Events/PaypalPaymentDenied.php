<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class PaypalPaymentDenied
{
    public function __construct(Order $order)
    {
    }
}
