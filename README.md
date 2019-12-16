# Laravel Attribute Events

## Quick Example

```php
class Order extends Model
{
    use AttributeEvents;

    protected $dispatchesEvents = [
        'created' => OrderPlaced::class,
        'status:canceled' => OrderCanceled::class,
        'note:*' => OrderNoteChanged::class,
    ];
}
```
