<?php

namespace Kleemans;

trait AttributeEvents
{
    /**
     * @var string[]
     */
    private $recordedEvents = [];

    public static function bootAttributeEvents()
    {
        static::saving(function ($model) {
            $model->recordAttributeEvents();
        });

        static::saved(function ($model) {
            $model->fireRecordedEvents();
        });
    }

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

    private function recordAttributeEvents(): void
    {
        if (!isset($this->dispatchesEvents)) {
            return;
        }

        if (!$this->exists) {
            return; // New instance, no attributes changed
        }

        foreach ($this->dispatchesEvents as $change => $eventClass) {
            if (strpos($change, ':') === false) {
                continue; // Not an attribute event
            }

            $exploded = explode(':', $change);
            $attribute = $exploded[0];
            $expected = $exploded[1];

            if (!isset($this->{$attribute})) {
                continue; // Attribute does not exist
            }

            if (!$this->isDirty($attribute)) {
                continue; // Attribute has not been changed
            }

            $value = $this->{$attribute};
            if (
                $expected === '*'
                || $expected === 'true' && $value === true
                || $expected === 'false' && $value === false
                || is_numeric($expected) && strpos($expected, '.') !== false && $value === floatval($expected) // float
                || is_numeric($expected) && $value === intval($expected) // int
                || $value === $expected
            ) {
                $this->recordEvent($change);
            }
        }
    }
}
