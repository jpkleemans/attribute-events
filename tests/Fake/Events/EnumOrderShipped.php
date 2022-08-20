<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\EnumOrder;
use Kleemans\Tests\Fake\Order;

class EnumOrderShipped
{
    public function __construct(EnumOrder $order)
    {
    }
}
