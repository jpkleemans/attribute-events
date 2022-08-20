<?php

namespace Kleemans\Tests\Fake;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Kleemans\AttributeEvents;

class EnumOrder extends Model
{
    use AttributeEvents;

    protected $table = 'orders';

    protected $attributes = [
        'status' => OrderStatus::PROCESSING,
        'shipping_address' => '',
        'billing_address' => '',
        'note' => '',
        'total' => 0.00,
        'paid_amount' => 0.00,
        'discount_percentage' => 0,
        'tax_free' => false,
        'payment_gateway' => 'credit_card',
        'meta' => '{}',
    ];

    protected $guarded = [];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    protected $dispatchesEvents = [
        'status:SHIPPED' => Events\EnumOrderShipped::class,
    ];
}
