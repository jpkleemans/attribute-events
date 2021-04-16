<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderDeleted
{
    public function __construct(Order $order)
    {
    }
}
