<?php

namespace IRPayment\Enums;

enum PaymentChannel: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
}
