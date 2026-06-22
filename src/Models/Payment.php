<?php

namespace IRPayment\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use IRPayment\Casts\PaymentMethodCast;
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
        'description',
        'phone',
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
        'payment_method' => PaymentMethodCast::class,
        'amount' => 'integer',
        'metadata' => 'array',
        'reference_id' => 'integer',
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

    public function scopeAuthorityKey(Builder $query, string $authorityKey): Builder
    {
        return $query->where('authority_key', $authorityKey);
    }

    /**
     * @see \IRPayment\Tests\PaymentChannelAttributeTest
     */
    public function online(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payment_channel === PaymentChannel::ONLINE
        );
    }

    /**
     * @see \IRPayment\Tests\PaymentChannelAttributeTest
     */
    public function offline(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payment_channel === PaymentChannel::OFFLINE
        );
    }
}
