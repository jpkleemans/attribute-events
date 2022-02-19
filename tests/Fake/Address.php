<?php

namespace Kleemans\Tests\Fake;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Address implements Castable
{
    public $street;
    public $city;

    public function __construct($street, $city)
    {
        $this->street = $street;
        $this->city = $city;
    }

    public function __toString()
    {
        return $this->street.', '.$this->city;
    }

    public static function castUsing(array $arguments)
    {
        return new class() implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if (!$value) {
                    return;
                }

                [$street, $city] = explode(', ', $value);

                return new Address($street, $city);
            }

            public function set($model, $key, $address, $attributes)
            {
                return [
                    'billing_address' => (string) $address,
                ];
            }
        };
    }
}
