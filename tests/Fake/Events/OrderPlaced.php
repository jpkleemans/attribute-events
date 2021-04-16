<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderPlaced
{
    public function __construct(Order $order)
    {
    }
}
