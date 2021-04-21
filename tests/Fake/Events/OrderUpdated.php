<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderUpdated
{
    public function __construct(Order $order)
    {
    }
}
