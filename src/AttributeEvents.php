<?php

namespace Kleemans;

trait AttributeEvents
{
    public static function bootAttributeEvents()
    {
        static::updated(function ($model) {
            $model->fireAttributeEvents();
        });
    }

    private function fireAttributeEvents(): void
    {
        if (!isset($this->dispatchesEvents)) {
            return;
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
                $this->fireModelEvent($change, false);
            }
        }
    }
}
