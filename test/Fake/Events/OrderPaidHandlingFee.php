<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderPaidHandlingFee
{
    public function __construct(Order $order)
    {
    }
}
