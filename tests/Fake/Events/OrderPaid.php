<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderPaid
{
    public function __construct(Order $order)
    {
    }
}
