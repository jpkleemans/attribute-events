<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderDeleted
{
    public function __construct(Order $order)
    {
    }
}
