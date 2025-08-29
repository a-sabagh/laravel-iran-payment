<?php

namespace IRPayment\Repositories;

use IRPayment\Models\Payment;

class PaymentRepository
{
    public function findByAuthorityKey(string $authorityKey)
    {
        return Payment::authorityKey($authorityKey)->first();
    }
}
