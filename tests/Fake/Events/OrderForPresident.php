<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderForPresident
{
    public function __construct(Order $order)
    {
    }
}
