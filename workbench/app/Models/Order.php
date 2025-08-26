<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use IRPayment\Models\Concerns\Paymentable;
use Workbench\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory, Paymentable;

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}
