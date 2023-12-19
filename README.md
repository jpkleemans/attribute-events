![Laravel Attribute Events](/../gh-pages/attribute-events.svg#gh-light-mode-only)
![Laravel Attribute Events](/../gh-pages/attribute-events-dark.svg#gh-dark-mode-only)

<p align="center">
  <a href="https://app.travis-ci.com/jpkleemans/attribute-events" target="_blank"><img src="https://img.shields.io/travis/com/jpkleemans/attribute-events?label=tests&style=flat-square" alt="Build Status"></a>
  <a href="https://github.com/jpkleemans/attribute-events/commits" target="_blank"><img src="https://img.shields.io/github/last-commit/jpkleemans/attribute-events?label=updated&style=flat-square" alt="Last Updated"></a>
  <a href="https://packagist.org/packages/jpkleemans/attribute-events"><img src="https://img.shields.io/packagist/v/jpkleemans/attribute-events?label=stable&style=flat-square" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/jpkleemans/attribute-events"><img src="https://img.shields.io/packagist/l/jpkleemans/attribute-events?style=flat-square" alt="License"></a>
</p>

```php
class Order extends Model
{
    protected $dispatchesEvents = [
        'status:shipped' => OrderShipped::class,
        'note:*' => OrderNoteChanged::class,
    ];
}
```

Eloquent models fire several handy events throughout their lifecycle, like `created` and `deleted`. However, there are usually many more business meaningful events that happen during a model's life. With this library you can capture those, by mapping attribute changes to your own event classes.

## Installation
```bash
composer require jpkleemans/attribute-events
```

## How to use it
Use the `Kleemans\AttributeEvents` trait in your model and add the attributes to the `$dispatchesEvents` property:

```php
class Order extends Model
{
    use AttributeEvents;

    protected $dispatchesEvents = [
        'created'         => OrderPlaced::class,
        'status:canceled' => OrderCanceled::class,
        'note:*'          => OrderNoteChanged::class,
    ];
}
```

The attribute events will be dispatched after the updated model is saved. Each event receives the instance of the model through its constructor.

> For more info on model events and the `$dispatchesEvents` property, visit the <a href="https://laravel.com/docs/eloquent#events" target="_blank">Laravel Docs</a>

## Listening
Now you can subscribe to the events via the `EventServiceProvider` `$listen` array, or manually with Closure based listeners:

```php
Event::listen(function (OrderCanceled $event) {
    // Restock inventory
});
```

Or push realtime updates to your users, using Laravel's <a href="https://laravel.com/docs/broadcasting" target="_blank">broadcasting</a> feature:

```js
Echo.channel('orders')
    .listen('OrderShipped', (event) => {
        // Display a notification
    })
```

## JSON attributes
For attributes stored as JSON, you can use the `->` operator:

```php
protected $dispatchesEvents = [
    'payment->status:completed' => PaymentCompleted::class,
];
```

## Accessors
For more complex state changes, you can use attributes defined by an <a href="https://laravel.com/docs/eloquent-mutators#defining-an-accessor" target="_blank">accessor</a>:

```php
class Product extends Model
{
    protected $dispatchesEvents = [
        'low_stock:true' => ProductReachedLowStock::class,
    ];

    public function getLowStockAttribute(): bool
    {
        return $this->stock <= 3;
    }
}
```

> You can also use the [new way of defining accessors](https://laravel.com/docs/9.x/releases#eloquent-accessors-and-mutators) introduced in Laravel 9.

## Learn more
- [“Decouple your Laravel code using Attribute Events”](https://jpkleemans.medium.com/decouple-your-laravel-code-using-attribute-events-de8f2528f46a) by Jan-Paul Kleemans
- [Laravel Docs on Model Events](https://laravel.com/docs/eloquent#events)

## Sponsors

<a href="https://www.nexxtmove.nl/" target="_blank">
  <img src="https://raw.githubusercontent.com/jpkleemans/attribute-events/gh-pages/nexxtmove-logo.svg" alt="Nexxtmove Logo" width="200">
</a>

Thanks to <a href="https://www.nexxtmove.nl/" target="_blank">Nexxtmove</a> for sponsoring the development of this project.  
Your logo or name here? [Sponsor this project](https://github.com/sponsors/jpkleemans).

## License

Code released under the [MIT License](https://github.com/jpkleemans/attribute-events/blob/master/LICENSE).
