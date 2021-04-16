<?php

namespace Kleemans\Tests\Unit;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Kleemans\Tests\Fake\Events\InvoiceDownloaded;
use Kleemans\Tests\Fake\Events\OrderCanceled;
use Kleemans\Tests\Fake\Events\OrderDeleted;
use Kleemans\Tests\Fake\Events\OrderMadeFree;
use Kleemans\Tests\Fake\Events\OrderMetaUpdated;
use Kleemans\Tests\Fake\Events\OrderNoteUpdated;
use Kleemans\Tests\Fake\Events\OrderPaid;
use Kleemans\Tests\Fake\Events\OrderPaidHandlingFee;
use Kleemans\Tests\Fake\Events\OrderPaidWithCash;
use Kleemans\Tests\Fake\Events\OrderPlaced;
use Kleemans\Tests\Fake\Events\OrderReturned;
use Kleemans\Tests\Fake\Events\OrderShipped;
use Kleemans\Tests\Fake\Events\OrderShippingCountryChanged;
use Kleemans\Tests\Fake\Events\OrderTaxCleared;
use Kleemans\Tests\Fake\Events\OrderUpdated;
use Kleemans\Tests\Fake\Events\PaypalPaymentDenied;
use Kleemans\Tests\Fake\Order;
use PHPUnit\Framework\TestCase;

class AttributeEventsTest extends TestCase
{
    private $dispatcher;

    private static $eventsToFake = [
        OrderPlaced::class,
        OrderUpdated::class,
        OrderDeleted::class,
        OrderNoteUpdated::class,
        OrderShipped::class,
        OrderCanceled::class,
        OrderReturned::class,
        OrderMadeFree::class,
        OrderPaidHandlingFee::class,
        OrderTaxCleared::class,
        OrderShippingCountryChanged::class,
        OrderPaid::class,
        OrderPaidWithCash::class,
        OrderMetaUpdated::class,
        PaypalPaymentDenied::class,
        InvoiceDownloaded::class,
    ];

    public function setUp(): void
    {
        $this->initEventDispatcher();
        $this->initDatabase();
    }

    /** @test */
    public function it_still_dispatches_native_events(): void
    {
        // Create
        $order = new Order();
        $order->save();

        $this->dispatcher->assertDispatched(OrderPlaced::class);

        // Update
        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertDispatched(OrderUpdated::class);

        // Delete
        $order->delete();

        $this->dispatcher->assertDispatched(OrderDeleted::class);
    }

    /** @test */
    public function it_dispatches_event_on_change()
    {
        $order = new Order();
        $order->save();

        $order->note = 'Please deliver before the weekend';
        $order->save();

        $this->dispatcher->assertDispatched(OrderNoteUpdated::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_after_find()
    {
        $order = Order::find(1);
        $order->note = 'Please deliver to neighbour';
        $order->save();

        $this->dispatcher->assertDispatched(OrderNoteUpdated::class);
    }

    /** @test */
    public function it_dispatches_multiple_events()
    {
        $order = new Order();
        $order->save();

        $order->note = 'Please deliver before the weekend';
        $order->status = 'shipped';
        $order->tax_free = true;
        $order->save();

        $this->dispatcher->assertDispatched(OrderShipped::class);
        $this->dispatcher->assertDispatched(OrderNoteUpdated::class);
        $this->dispatcher->assertDispatched(OrderTaxCleared::class);
    }

    /** @test */
    public function it_works_with_update_method()
    {
        $order = Order::find(1);
        $order->update([
            'note' => 'Handle with care',
            'status' => 'canceled',
        ]);

        $this->dispatcher->assertDispatched(OrderNoteUpdated::class);
        $this->dispatcher->assertDispatched(OrderCanceled::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_initial_value_of_attribute()
    {
        $order = new Order();
        $order->note = 'Please handle with care';
        $order->save();

        $this->dispatcher->assertNotDispatched(OrderNoteUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_when_same_value()
    {
        $order = new Order();
        $order->note = 'Please handle with care';
        $order->save();

        $order->note = 'Please handle with care';
        $order->save();

        $this->dispatcher->assertNotDispatched(OrderNoteUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_change_of_other_attribute()
    {
        $order = new Order();
        $order->note = 'Please handle with care';
        $order->save();

        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertNotDispatched(OrderNoteUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_initial_save_of_replication()
    {
        $order = Order::find(1);

        $orderCopy = $order->replicate();
        $orderCopy->status = 'canceled';
        $orderCopy->save();

        $this->dispatcher->assertNotDispatched(OrderCanceled::class);
    }

    /** @test */
    public function it_dispatches_correct_number_of_times()
    {
        $order = new Order();
        $order->note = 'Initial value';
        $order->save();

        $order->note = 'Change 1';
        $order->save();

        $order->note = 'Change 2';
        $order->save();

        $order->note = 'Change 2';
        $order->save();

        $this->dispatcher->assertDispatchedTimes(OrderNoteUpdated::class, 2);
        $this->dispatcher->assertDispatchedTimes(OrderUpdated::class, 2);
        $this->dispatcher->assertDispatchedTimes(OrderPlaced::class, 1);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_string_value()
    {
        $order = new Order();
        $order->save();

        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertDispatched(OrderShipped::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_int_value()
    {
        $order = new Order();
        $order->save();

        $order->discount_percentage = 100;
        $order->save();

        $this->dispatcher->assertDispatched(OrderMadeFree::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_float_value()
    {
        $order = new Order();
        $order->save();

        $order->paid_amount = 2.99;
        $order->save();

        $this->dispatcher->assertDispatched(OrderPaidHandlingFee::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_boolean_value()
    {
        $order = new Order();
        $order->save();

        $order->tax_free = true;
        $order->save();

        $this->dispatcher->assertDispatched(OrderTaxCleared::class);
    }

    /** @test */
    public function it_respects_withoutEvents()
    {
        Order::withoutEvents(function () {
            $order = Order::find(1);
            $order->status = 'returned';
            $order->save();

            $this->dispatcher->assertNotDispatched(OrderReturned::class);
        });
    }

    // Accessors

    /** @test */
    public function it_dispatches_event_on_accessor_change()
    {
        $order = new Order();
        $order->shipping_address = '4073 Hamill Avenue US';
        $order->save();

        $order = new Order();
        $order->shipping_address = '9819 Second Ave. US';
        $order->save();

        $this->dispatcher->assertNotDispatched(OrderShippingCountryChanged::class);

        $order->shipping_address = 'Kerkstraat 7 NL';
        $order->save();

        $this->dispatcher->assertDispatched(OrderShippingCountryChanged::class);
    }

    /** @test */
    public function it_dispatches_event_on_accessor_change_to_specific_value()
    {
        $order = new Order();
        $order->total = 10;
        $order->save();

        $order->paid_amount = 5;
        $order->save();

        $this->dispatcher->assertNotDispatched(OrderPaid::class);

        $order->paid_amount = 10;
        $order->save();

        $this->dispatcher->assertDispatched(OrderPaid::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_of_accessor_for_existing_attribute()
    {
        $order = Order::find(1);
        $order->payment_gateway = 'direct';
        $order->save();

        $this->dispatcher->assertDispatched(OrderPaidWithCash::class);
    }

    // JSON attributes

    /** @test */
    public function it_dispatches_event_when_updating_json_attribute()
    {
        $order = new Order();
        $order->meta = ['gift_wrapping' => true];
        $order->save();

        $order->meta = ['gift_wrapping' => false];
        $order->save();

        $this->dispatcher->assertDispatched(OrderMetaUpdated::class);
    }

    /** @test */
    public function it_dispatches_event_when_updating_json_field()
    {
        $order = new Order();
        $order->meta = ['paypal_status' => 'pending'];
        $order->save();

        $meta = $order->meta;
        $meta['paypal_status'] = 'denied';
        $order->meta = $meta;

        $order->save();

        $this->dispatcher->assertDispatched(PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_works_with_json_changes_through_update_method()
    {
        $order = new Order();
        $order->meta = ['paypal_status' => 'pending'];
        $order->save();

        $order->update(['meta->paypal_status' => 'denied']);

        $this->dispatcher->assertDispatched(PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_dispatches_event_when_adding_json_field()
    {
        $order = new Order();
        $order->meta = ['gift_wrapping' => true];
        $order->save();

        $meta = $order->meta;
        $meta['paypal_status'] = 'denied';
        $order->meta = $meta;

        $order->save();

        $this->dispatcher->assertDispatched(PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_initial_value_of_json_attribute()
    {
        $order = new Order();
        $order->meta = [
            'gift_wrapping' => true,
            'paypal_status' => 'denied',
        ];
        $order->save();

        $this->dispatcher->assertNotDispatched(OrderMetaUpdated::class);
        $this->dispatcher->assertNotDispatched(PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_works_with_nested_json_fields()
    {
        $order = new Order();
        $order->meta = ['invoice' => ['downloaded' => false]];
        $order->save();

        $meta = $order->meta;
        $meta['invoice']['downloaded'] = true;
        $order->meta = $meta;

        $order->save();

        $this->dispatcher->assertDispatched(OrderMetaUpdated::class);
        $this->dispatcher->assertDispatched(InvoiceDownloaded::class);
    }

    // Setup methods

    private function initEventDispatcher()
    {
        $this->dispatcher = new EventFake(new Dispatcher(), self::$eventsToFake);

        Model::clearBootedModels();
        Model::setEventDispatcher($this->dispatcher);
    }

    private function initDatabase()
    {
        $db = new DB();
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $db->bootEloquent();
        $db->setAsGlobal();

        $this->migrate();
        $this->seed();
    }

    private function migrate()
    {
        DB::schema()->create('orders', function ($table) {
            $table->increments('id');
            $table->string('status');
            $table->string('shipping_address');
            $table->text('note');
            $table->decimal('total', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->integer('discount_percentage');
            $table->boolean('tax_free');
            $table->string('payment_gateway');
            $table->json('meta');
            $table->timestamps();
        });
    }

    private function seed()
    {
        DB::table('orders')->insert([
            [
                'id' => 1,
                'status' => 'processing',
                'shipping_address' => '',
                'note' => '',
                'total' => 0.00,
                'paid_amount' => 0.00,
                'discount_percentage' => 0,
                'tax_free' => false,
                'payment_gateway' => 'credit_card',
                'meta' => '{}',
            ],
        ]);
    }
}
