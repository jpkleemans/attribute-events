<?php

namespace Kleemans\Tests\Fake;

enum OrderStatus: int
{
    case PROCESSING = 1;
    case SHIPPED = 2;
    case CANCELLED = 3;
}
