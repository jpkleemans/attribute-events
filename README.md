# Laravel Attribute Events

```php
class Order extends Model
{
    protected $dispatchesEvents = [
        'status:canceled' => OrderCanceled::class,
        'note:*' => OrderNoteChanged::class,
    ];
}
```

Eloquent models fire several handy events throughout their lifecycle, like `created` and `deleted`. However, there are usually many more interesting events that occur during a model's life. With this library you can add those, by mapping attribute changes of your model to meaningful events.

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

The attribute events will be dispatched after the model is saved. Each event receives the instance of the model through its constructor.

> For more info on model events and the `$dispatchesEvents` property, visit the [Laravel Docs](https://laravel.com/docs/eloquent#events)

## Accessors (WIP)
For more complex state changes, you can use attributes defined by an [accessor](https://laravel.com/docs/eloquent-mutators#defining-an-accessor):

```php
class Product extends Model
{
    use AttributeEvents;

    protected $dispatchesEvents = [
        'low_stock:true' => ProductReachedLowStock::class,
    ];

    public function getLowStockAttribute(): bool
    {
        return $this->stock <= 3;
    }
}
```
