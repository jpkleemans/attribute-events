<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class EnumOrderShipped
{
    public function __construct(Order $order)
    {
    }
}
