<?php

namespace IRPayment\Contracts;

interface Factory
{
    public function driver($driver = null);
}
