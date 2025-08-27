<?php

namespace IRPayment\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETE = 'complete';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
