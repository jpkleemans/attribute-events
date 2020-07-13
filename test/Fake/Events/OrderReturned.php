<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderReturned
{
    public function __construct(Order $order)
    {
    }
}
