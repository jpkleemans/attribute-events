<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class InvoiceDownloaded
{
    public function __construct(Order $order)
    {
    }
}
