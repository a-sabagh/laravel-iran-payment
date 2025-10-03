<?php

namespace IRPayment\Enums;

use Enhance\Enums\Concerns\Translatable;

enum PaymentChannel: string
{
    use Translatable;

    public function namespace(): string
    {
        return 'irpayment::';
    }

    case ONLINE = 'online';
    case OFFLINE = 'offline';
}
