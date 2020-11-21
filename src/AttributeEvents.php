<?php

namespace Kleemans;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        foreach ($this->getAttributeEvents()  as $change => $event) {
            [$attribute, $expected] = explode(':', $change);

            $value = $this->getAttribute($attribute);

            // Accessor
            if ($this->hasGetMutator($attribute)) {
                if (!$this->isDirtyAccessor($attribute)) {
                    continue; // Not changed
                }
            }

            // JSON attribute
            elseif (Str::contains($attribute, '->')) {
                [$attribute, $path] = explode('->', $attribute, 2);
                $path = str_replace('->', '.', $path);

                if (!$this->isDirtyNested($attribute, $path)) {
                    continue; // Not changed
                }

                $value = Arr::get($this->getAttribute($attribute), $path);
            }

            // Regular attribute
            elseif (!$this->isDirty($attribute)) {
                continue; // Not changed
            }

            if (
                $expected === '*'
                || $expected === 'true' && $value === true
                || $expected === 'false' && $value === false
                || is_numeric($expected) && Str::contains($expected, '.') && $value === (float) $expected // float
                || is_numeric($expected) && $value === (int) $expected // int
                || $value === $expected
            ) {
                $this->fireModelEvent($change, false);
            }
        }
    }

    private function syncOriginalAccessors(): void
    {
        foreach ($this->getAttributeEvents() as $change => $event) {
            [$attribute] = explode(':', $change);

            if (!$this->hasGetMutator($attribute)) {
                continue; // Attribute does not have accessor
            }

            $value = $this->getAttribute($attribute);
            if ($value === null) {
                continue; // Attribute does not exist
            }

            $this->originalAccessors[$attribute] = $value;
        }
    }

    public function isDirtyAccessor(string $attribute): bool
    {
        if (!isset($this->originalAccessors[$attribute])) {
            return false; // Attribute does not have a original value saved
        }

        $originalValue = $this->originalAccessors[$attribute];
        $currentValue = $this->getAttribute($attribute);

        return $originalValue !== $currentValue;
    }

    public function isDirtyNested(string $attribute, string $path): bool
    {
        $originalValue = Arr::get($this->getOriginal($attribute), $path);
        $currentValue = Arr::get($this->getAttribute($attribute), $path);

        if ($currentValue === null) {
            return false;
        }

        return $originalValue !== $currentValue;
    }

    /**
     * @return array<string, string>
     */
    private function getAttributeEvents(): iterable
    {
        foreach ($this->dispatchesEvents as $change => $event) {
            if (!Str::contains($change, ':')) {
                continue; // Not an attribute event
            }

            yield $change => $event;
        }
    }
}
