<?php

namespace IRPayment\Models\Traits;

use IRPayment\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Paymentable
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
