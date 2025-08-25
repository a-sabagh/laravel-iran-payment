<?php

namespace IRPayment\Models;

use IRPayment\Enums\PaymentChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'paymentable_type',
        'paymentable_id',
        'payment_channel',
        'payment_method',
        'code',
        'message',
        'card_hash',
        'card_mask',
        'reference_id',
        'amount',
        'metadata',
    ];

    protected $casts = [
        'code' => 'integer',
        'payment_channel' => PaymentChannel::class,
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }
}
