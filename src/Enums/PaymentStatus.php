<?php

namespace IRPayment\Enums;

use Enhance\Enums\Concerns\Badgeable;
use Enhance\Enums\Concerns\Translatable;
use Illuminate\Support\Collection;

enum PaymentStatus: string
{
    use Translatable, Badgeable;

    public function getBadgeCollection(): Collection
    {
        return collect([
            'PENDING' => 'info',
            'PROCESSING' => 'primary',
            'COMPLETE' => 'success',
            'FAILED' => 'dark',
            'CANCELED' => 'secondary',
        ]);
    }

    public function namespace(): string
    {
        return 'irpayment';
    }

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETE = 'complete';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
