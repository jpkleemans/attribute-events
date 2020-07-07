<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderPaidWithCash
{
    public function __construct(Order $order)
    {
    }
}
