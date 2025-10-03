<?php

namespace IRPayment\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use IRPayment\DTO\PaymentMethodValueObject;

class PaymentMethodCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        if (! $value) {
            return;
        }

        return new PaymentMethodValueObject($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return (string) $value;
    }
}
