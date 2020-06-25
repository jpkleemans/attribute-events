<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderTaxCleared
{
    public function __construct(Order $order)
    {
    }
}
