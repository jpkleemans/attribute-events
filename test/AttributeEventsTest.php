<?php

namespace Kleemans\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Testing\Fakes\EventFake;
use Kleemans\Test\Fake;
use Illuminate\Database\Capsule\Manager as DB;

class AttributeEventsTest extends TestCase
{
    private $dispatcher;

    private static $eventsToFake = [
        Fake\Events\OrderPlaced::class,
        Fake\Events\OrderUpdated::class,
        Fake\Events\OrderDeleted::class,
        Fake\Events\OrderNoteUpdated::class,
        Fake\Events\OrderShipped::class,
        Fake\Events\OrderCanceled::class,
        Fake\Events\OrderReturned::class,
        Fake\Events\OrderMadeFree::class,
        Fake\Events\OrderPaidHandlingFee::class,
        Fake\Events\OrderTaxCleared::class,
        Fake\Events\OrderShippingCountryChanged::class,
        Fake\Events\OrderPaid::class,
        Fake\Events\OrderPaidWithCash::class,
        Fake\Events\OrderMetaUpdated::class,
        Fake\Events\PaypalPaymentDenied::class,
        Fake\Events\InvoiceDownloaded::class,
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
        $order = new Fake\Order();
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderPlaced::class);

        // Update
        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderUpdated::class);

        // Delete
        $order->delete();

        $this->dispatcher->assertDispatched(Fake\Events\OrderDeleted::class);
    }

    /** @test */
    public function it_dispatches_event_on_change()
    {
        $order = new Fake\Order();
        $order->save();

        $order->note = 'Please deliver before the weekend';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderNoteUpdated::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_after_find()
    {
        $order = Fake\Order::find(1);
        $order->note = 'Please deliver to neighbour';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderNoteUpdated::class);
    }

    /** @test */
    public function it_dispatches_multiple_events()
    {
        $order = new Fake\Order();
        $order->save();

        $order->note = 'Please deliver before the weekend';
        $order->status = 'shipped';
        $order->tax_free = true;
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderShipped::class);
        $this->dispatcher->assertDispatched(Fake\Events\OrderNoteUpdated::class);
        $this->dispatcher->assertDispatched(Fake\Events\OrderTaxCleared::class);
    }

    /** @test */
    public function it_works_with_update_method()
    {
        $order = Fake\Order::find(1);
        $order->update([
            'note' => 'Handle with care',
            'status' => 'canceled',
        ]);

        $this->dispatcher->assertDispatched(Fake\Events\OrderNoteUpdated::class);
        $this->dispatcher->assertDispatched(Fake\Events\OrderCanceled::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_initial_value_of_attribute()
    {
        $order = new Fake\Order();
        $order->note = 'Please handle with care';
        $order->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderNoteUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_when_same_value()
    {
        $order = new Fake\Order();
        $order->note = 'Please handle with care';
        $order->save();

        $order->note = 'Please handle with care';
        $order->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderNoteUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_change_of_other_attribute()
    {
        $order = new Fake\Order();
        $order->note = 'Please handle with care';
        $order->save();

        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderNoteUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_initial_save_of_replication()
    {
        $order = Fake\Order::find(1);

        $orderCopy = $order->replicate();
        $orderCopy->status = 'canceled';
        $orderCopy->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderCanceled::class);
    }

    /** @test */
    public function it_dispatches_correct_number_of_times()
    {
        $order = new Fake\Order();
        $order->note = 'Initial value';
        $order->save();

        $order->note = 'Change 1';
        $order->save();

        $order->note = 'Change 2';
        $order->save();

        $order->note = 'Change 2';
        $order->save();

        $this->dispatcher->assertDispatchedTimes(Fake\Events\OrderNoteUpdated::class, 2);
        $this->dispatcher->assertDispatchedTimes(Fake\Events\OrderUpdated::class, 2);
        $this->dispatcher->assertDispatchedTimes(Fake\Events\OrderPlaced::class, 1);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_string_value()
    {
        $order = new Fake\Order();
        $order->save();

        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderShipped::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_int_value()
    {
        $order = new Fake\Order();
        $order->save();

        $order->discount_percentage = 100;
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderMadeFree::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_float_value()
    {
        $order = new Fake\Order();
        $order->save();

        $order->paid_amount = 2.99;
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderPaidHandlingFee::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_specific_boolean_value()
    {
        $order = new Fake\Order();
        $order->save();

        $order->tax_free = true;
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderTaxCleared::class);
    }

    /** @test */
    public function it_respects_withoutEvents()
    {
        Fake\Order::withoutEvents(function () {
            $order = Fake\Order::find(1);
            $order->status = 'returned';
            $order->save();

            $this->dispatcher->assertNotDispatched(Fake\Events\OrderReturned::class);
        });
    }

    // Accessors

    /** @test */
    public function it_dispatches_event_on_accessor_change()
    {
        $order = new Fake\Order();
        $order->shipping_address = '4073 Hamill Avenue US';
        $order->save();

        $order = new Fake\Order();
        $order->shipping_address = '9819 Second Ave. US';
        $order->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderShippingCountryChanged::class);

        $order->shipping_address = 'Kerkstraat 7 NL';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderShippingCountryChanged::class);
    }

    /** @test */
    public function it_dispatches_event_on_accessor_change_to_specific_value()
    {
        $order = new Fake\Order();
        $order->total = 10;
        $order->save();

        $order->paid_amount = 5;
        $order->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderPaid::class);

        $order->paid_amount = 10;
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderPaid::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_of_accessor_for_existing_attribute()
    {
        $order = Fake\Order::find(1);
        $order->payment_gateway = 'direct';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderPaidWithCash::class);
    }

    // JSON attributes

    /** @test */
    public function it_dispatches_event_when_updating_json_attribute()
    {
        $order = new Fake\Order();
        $order->meta = ['gift_wrapping' => true];
        $order->save();

        $order->meta = ['gift_wrapping' => false];
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderMetaUpdated::class);
    }

    /** @test */
    public function it_dispatches_event_when_updating_json_field()
    {
        $order = new Fake\Order();
        $order->meta = ['paypal_status' => 'pending'];
        $order->save();

        $meta = $order->meta;
        $meta['paypal_status'] = 'denied';
        $order->meta = $meta;

        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_works_with_json_changes_through_update_method()
    {
        $order = new Fake\Order();
        $order->meta = ['paypal_status' => 'pending'];
        $order->save();

        $order->update(['meta->paypal_status' => 'denied']);

        $this->dispatcher->assertDispatched(Fake\Events\PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_dispatches_event_when_adding_json_field()
    {
        $order = new Fake\Order();
        $order->meta = ['gift_wrapping' => true];
        $order->save();

        $meta = $order->meta;
        $meta['paypal_status'] = 'denied';
        $order->meta = $meta;

        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_does_not_dispatch_on_initial_value_of_json_attribute()
    {
        $order = new Fake\Order();
        $order->meta = [
            'gift_wrapping' => true,
            'paypal_status' => 'denied',
        ];
        $order->save();

        $this->dispatcher->assertNotDispatched(Fake\Events\OrderMetaUpdated::class);
        $this->dispatcher->assertNotDispatched(Fake\Events\PaypalPaymentDenied::class);
    }

    /** @test */
    public function it_works_with_nested_json_fields()
    {
        $order = new Fake\Order();
        $order->meta = ['invoice' => ['downloaded' => false]];
        $order->save();

        $meta = $order->meta;
        $meta['invoice']['downloaded'] = true;
        $order->meta = $meta;

        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderMetaUpdated::class);
        $this->dispatcher->assertDispatched(Fake\Events\InvoiceDownloaded::class);
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
            ]
        ]);
    }
}
