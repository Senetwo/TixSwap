<?php

namespace App\Services\Mpesa;

/**
 * The final outcome of an STK push, from either Safaricom's callback or
 * an STK query. ResultCode 0 means the buyer paid.
 */
final class StkResult
{
    public function __construct(
        public readonly string $checkoutRequestId,
        public readonly int $resultCode,
        public readonly string $resultDesc,
        public readonly ?int $amount = null,
        public readonly ?string $receiptNumber = null,
        public readonly ?string $phoneNumber = null,
        public readonly array $raw = [],
    ) {}

    public function successful(): bool
    {
        return $this->resultCode === 0;
    }

    /**
     * Parse the JSON body Safaricom POSTs to the callback URL.
     */
    public static function fromCallback(array $payload): ?self
    {
        $callback = $payload['Body']['stkCallback'] ?? null;

        if (! is_array($callback) || ! isset($callback['CheckoutRequestID'], $callback['ResultCode'])) {
            return null;
        }

        $meta = [];
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (isset($item['Name'])) {
                $meta[$item['Name']] = $item['Value'] ?? null;
            }
        }

        return new self(
            checkoutRequestId: (string) $callback['CheckoutRequestID'],
            resultCode: (int) $callback['ResultCode'],
            resultDesc: (string) ($callback['ResultDesc'] ?? ''),
            amount: isset($meta['Amount']) ? (int) round((float) $meta['Amount']) : null,
            receiptNumber: isset($meta['MpesaReceiptNumber']) ? (string) $meta['MpesaReceiptNumber'] : null,
            phoneNumber: isset($meta['PhoneNumber']) ? (string) $meta['PhoneNumber'] : null,
            raw: $payload,
        );
    }
}
