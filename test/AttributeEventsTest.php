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

    public function setUp(): void
    {
        $this->initDatabase();
        $this->initEventDispatcher();
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

        $this->createTables();
    }

    private function createTables()
    {
        DB::schema()->create('orders', function ($table) {
            $table->increments('id');
            $table->string('status');
            $table->timestamps();
        });
    }

    private function initEventDispatcher()
    {
        $this->dispatcher = new EventFake(new Dispatcher());
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
        // TODO
    }

    /** @test */
    public function it_dispatches_event_on_change_to_string_value()
    {
        // TODO
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
