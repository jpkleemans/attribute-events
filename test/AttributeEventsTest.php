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
    ];

    public function setUp(): void
    {
        $this->initEventDispatcher();
        $this->initDatabase();
    }

    private function initDatabase()
    {
        $db = new DB;
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
            $table->timestamps();
        });
    }

    private function seed()
    {
        Fake\Order::create();
    }

    private function initEventDispatcher()
    {
        $this->dispatcher = new EventFake(new Dispatcher(), self::$eventsToFake);

        Model::clearBootedModels();
        Model::setEventDispatcher($this->dispatcher);
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
        $order->note = 'Please deliver before the weekend';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderNoteUpdated::class);
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
    public function it_dispatches_event_on_accessor_change()
    {
        $order = new Fake\Order();
        $order->shipping_address = '4073 Hamill Avenue US';
        $order->save();

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
}
