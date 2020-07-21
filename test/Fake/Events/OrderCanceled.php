<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderCanceled
{
    public function __construct(Order $order)
    {
    }
}
