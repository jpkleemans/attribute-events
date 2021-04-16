<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class InvoiceDownloaded
{
    public function __construct(Order $order)
    {
    }
}
