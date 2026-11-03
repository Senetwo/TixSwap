<?php

namespace App\Services\Mpesa;

final class StkPushResponse
{
    public function __construct(
        public readonly string $checkoutRequestId,
        public readonly string $merchantRequestId,
        public readonly string $customerMessage,
    ) {}
}
