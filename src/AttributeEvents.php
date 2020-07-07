<?php

namespace Kleemans;

trait AttributeEvents
{
    private $originalAccessors = [];

    public static function bootAttributeEvents(): void
    {
        static::updated(function ($model) {
            $model->fireAttributeEvents();
        });

        static::retrieved(function ($model) {
            $model->syncOriginalAccessors();
        });

        static::saved(function ($model) {
            $model->syncOriginalAccessors();
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

            if ($this->hasGetMutator($attribute)) {
                if (!$this->isDirtyAccessor($attribute)) {
                    continue; // Accessor has not been changed
                }
            } elseif (!$this->isDirty($attribute)) {
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

    private function syncOriginalAccessors(): void
    {
        foreach ($this->dispatchesEvents as $change => $eventClass) {
            if (strpos($change, ':') === false) {
                continue; // Not an attribute event
            }

            $exploded = explode(':', $change);
            $attribute = $exploded[0];

            if (!$this->hasGetMutator($attribute)) {
                continue; // Attribute does not have accessor
            }

            if (!isset($this->{$attribute})) {
                continue; // Attribute does not exist
            }

            $value = $this->{$attribute};

            $this->originalAccessors[$attribute] = $value;
        }
    }

    private function isDirtyAccessor(string $attribute): bool
    {
        if (!isset($this->originalAccessors[$attribute])) {
            return false; // Attribute does not have a original value saved
        }

        $originalValue = $this->originalAccessors[$attribute];
        $currentValue = $this->{$attribute};

        return $originalValue !== $currentValue;
    }
}
