<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderShippingCountryChanged
{
    public function __construct(Order $order)
    {
    }
}
