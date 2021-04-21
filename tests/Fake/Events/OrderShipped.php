<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderShipped
{
    public function __construct(Order $order)
    {
    }
}
