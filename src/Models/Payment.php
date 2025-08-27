<?php

namespace IRPayment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use IRPayment\Database\Factories\PaymentFactory;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Enums\PaymentStatus;

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
        'authority_key',
        'reference_id',
        'amount',
        'status',
        'metadata',
    ];

    protected $casts = [
        'code' => 'integer',
        'payment_channel' => PaymentChannel::class,
        'status' => PaymentStatus::class,
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory()
    {
        return PaymentFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'authority_key';
    }
}
