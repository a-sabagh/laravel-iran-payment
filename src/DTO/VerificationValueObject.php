<?php

namespace IRPayment\DTO;

class VerificationValueObject
{
    public function __construct(
        public int $code,
        public ?int $referenceId,
        public string $message,
        public ?string $cardHash,
        public ?string $cardMask
    ) {}

    public function isSuccess(): bool
    {
        return in_array($this->code, [100, 101]);
    }

    public function isFailed(): bool
    {
        return ! $this->isSuccess();
    }

    public function isVerified(): bool
    {
        return ! 100 == $this->code;
    }

    public function isAlreadyVerified(): bool
    {
        return ! 101 == $this->code;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'reference_id' => $this->referenceId,
            'card_hash' => $this->cardHash,
            'card_mask' => $this->cardMask,
        ];
    }
}
