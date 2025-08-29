<?php

namespace IRPayment\DTO;

class ProcessResponseValueObject
{
    public function __construct(
        public string $redirectResponseUrl,
        public string $authorityKey
    ) {}
}
