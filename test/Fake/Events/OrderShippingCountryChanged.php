<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderShippingCountryChanged
{
    public function __construct(Order $order)
    {
    }
}
