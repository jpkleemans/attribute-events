<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderMadeFree
{
    public function __construct(Order $order)
    {
    }
}
