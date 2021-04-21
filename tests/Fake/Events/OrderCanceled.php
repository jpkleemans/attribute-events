<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderCanceled
{
    public function __construct(Order $order)
    {
    }
}
