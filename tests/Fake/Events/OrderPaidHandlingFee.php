<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderPaidHandlingFee
{
    public function __construct(Order $order)
    {
    }
}
