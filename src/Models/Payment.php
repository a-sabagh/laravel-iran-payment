<?php

namespace IRPayment\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use IRPayment\Casts\PaymentMethodCast;
use IRPayment\Database\Factories\PaymentFactory;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Enums\PaymentStatus;

/**
 * @property-read bool $online
 * @property-read bool $offline
 * @property-read bool $completed
 * @property-read bool $failed
 */
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

    /**
     * @see \IRPayment\Tests\PaymentHasStatusTest
     */
    public function hasStatus(PaymentStatus|string|array $status): bool
    {
        $statuses = Arr::wrap($status);

        $normalizedClusore = function ($value) {
            if ($value instanceof PaymentStatus) {
                return $value;
            }

            if (is_string($value)) {
                return PaymentStatus::tryFrom($value);
            }

            return null;
        };

        $normalized = collect($statuses)
            ->map($normalizedClusore)
            ->filter()
            ->all();

        return in_array($this->status, $normalized, true);
    }

    /**
     * @see \IRPayment\Tests\PaymentHasStatusTest
     */
    public function missingStatus(PaymentStatus|string|array $status): bool
    {
        return ! $this->hasStatus($status);
    }

    /**
     * @see \IRPayment\Tests\PaymentHasStatusTest
     */
    public function completed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hasStatus(PaymentStatus::COMPLETE)
        );
    }

    /**
     * @see \IRPayment\Tests\PaymentHasStatusTest
     */
    public function failed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hasStatus(PaymentStatus::FAILED)
        );
    }
}
