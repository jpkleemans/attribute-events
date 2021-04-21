<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderReturned
{
    public function __construct(Order $order)
    {
    }
}
