<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderPlaced
{
    public function __construct(Order $order)
    {
    }
}
