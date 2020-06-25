<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderPaid
{
    public function __construct(Order $order)
    {
    }
}
