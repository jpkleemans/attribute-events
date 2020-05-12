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
            $table->text('note');
            $table->timestamps();
        });
    }

    private function seed()
    {
        DB::table('orders')->insert([
            ['id' => 1, 'status' => 'processing', 'note' => '']
        ]);
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
    public function it_dispatches_event_on_change_to_string_value()
    {
        $order = new Fake\Order();
        $order->save();

        $order->status = 'shipped';
        $order->save();

        $this->dispatcher->assertDispatched(Fake\Events\OrderShipped::class);
    }

    /** @test */
    public function it_dispatches_event_on_change_to_int_value()
    {
        // TODO
    }

    /** @test */
    public function it_dispatches_event_on_change_to_boolean_value()
    {
        // TODO
    }

    /** @test */
    public function it_dispatches_event_on_accessor_change()
    {
        // TODO
    }

    /** @test */
    public function it_dispatches_event_on_accessor_change_to_specific_value()
    {
        // TODO
    }
}
