<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderMetaUpdated
{
    public function __construct(Order $order)
    {
    }
}
