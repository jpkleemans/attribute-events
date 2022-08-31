<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\EnumOrder;

class EnumOrderShipped
{
    public function __construct(EnumOrder $order)
    {
    }
}
