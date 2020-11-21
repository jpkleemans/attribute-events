<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class PaypalPaymentDenied
{
    public function __construct(Order $order)
    {
    }
}
