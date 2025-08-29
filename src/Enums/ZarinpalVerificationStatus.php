<?php

namespace IRPayment\Enums;

enum ZarinpalVerificationStatus: string
{
    case CANCELED = 'NOK';
    case SUCCESS = 'OK';
}
