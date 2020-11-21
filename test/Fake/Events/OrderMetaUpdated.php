<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderMetaUpdated
{
    public function __construct(Order $order)
    {
    }
}
