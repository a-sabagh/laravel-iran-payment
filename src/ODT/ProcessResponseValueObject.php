<?php

namespace IRPayment\ODT;

class ProcessResponseValueObject
{
    public function __construct(
        public string $redirectResponseUrl,
        public string $authorityKey
    ) {}
}
