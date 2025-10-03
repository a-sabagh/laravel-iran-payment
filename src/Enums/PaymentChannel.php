<?php

namespace IRPayment\Enums;

use Enhance\Enums\Concerns\Translatable;

enum PaymentChannel: string
{
    use Translatable;

    case ONLINE = 'online';
    case OFFLINE = 'offline';
}
