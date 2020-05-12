<?php

namespace Kleemans\Test\Fake\Events;

use Kleemans\Test\Fake\Order;

class OrderNoteUpdated
{
    public function __construct(Order $order)
    {
    }
}
