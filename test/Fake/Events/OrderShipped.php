<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderShipped
{
    public function __construct(Order $order)
    {
    }
}
