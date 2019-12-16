<?php

namespace Kleemans\AttributeEvents;

trait AttributeEvents
{
    /**
     * @var string[]
     */
    private static $nativeEvents = [
        'retrieved',
        'creating',
        'created',
        'updating',
        'updated',
        'saving',
        'saved',
        'restoring',
        'restored',
        'replicating',
        'deleting',
        'deleted',
        'forceDeleted',
    ];

    /**
     * @var string[]
     */
    private $recordedEvents = [];

    private function recordEvent(string $event): void
    {
        $this->recordedEvents[] = $event;
    }

    private function clearRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }

    private function fireRecordedEvents(): void
    {
        foreach ($this->recordedEvents as $event) {
            $this->fireModelEvent($event, false);
        }

        $this->clearRecordedEvents();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            $model->recordAttributeEvents();
        });

        static::saved(function ($model) {
            $model->fireRecordedEvents();
        });
    }

    private function recordAttributeEvents(): void
    {
        if (!isset($this->dispatchesEvents)) {
            return;
        }

        $attributeEvents = array_diff_key($this->dispatchesEvents, self::$nativeEvents);

        foreach ($attributeEvents as $change => $eventClass) {
            $exploded = explode(':', $change);
            $attribute = $exploded[0];
            $value = $exploded[1] ?? '*';

            if (!$this->isDirty($attribute)) {
                continue;
            }

            if ($value === '*') {
                $this->recordEvent($change);

                continue;
            }

            if ($this->{$attribute} === $value) {
                $this->recordEvent($change);
            }
        }
    }
}
