<?php

namespace Kleemans\Tests\Fake\Events;

use Kleemans\Tests\Fake\Order;

class OrderNoteUpdated
{
    public $oldValue;
    public $newValue;

    public function __construct(Order $order)
    {
        $this->oldValue = $order->getOriginal('note');
        $this->newValue = $order->note;
    }
}
