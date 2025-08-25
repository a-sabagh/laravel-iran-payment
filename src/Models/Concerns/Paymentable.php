<?php

namespace IRPayment\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use IRPayment\Models\Payment;

trait Paymentable
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
