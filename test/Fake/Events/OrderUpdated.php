<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderUpdated
{
    public function __construct(Order $order)
    {
    }
}
