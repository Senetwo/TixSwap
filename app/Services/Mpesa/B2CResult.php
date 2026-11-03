<?php

namespace App\Services\Mpesa;

/**
 * Outcome of a B2C payment, from Safaricom's result callback.
 */
final class B2CResult
{
    public function __construct(
        public readonly string $originatorConversationId,
        public readonly int $resultCode,
        public readonly string $resultDesc,
        public readonly ?string $receipt = null,
        public readonly ?int $amount = null,
        public readonly array $raw = [],
    ) {}

    public function successful(): bool
    {
        return $this->resultCode === 0;
    }

    public static function fromCallback(array $payload): ?self
    {
        $result = $payload['Result'] ?? null;

        if (! is_array($result) || ! isset($result['OriginatorConversationID'], $result['ResultCode'])) {
            return null;
        }

        $params = [];
        foreach ($result['ResultParameters']['ResultParameter'] ?? [] as $p) {
            if (isset($p['Key'])) {
                $params[$p['Key']] = $p['Value'] ?? null;
            }
        }

        return new self(
            originatorConversationId: (string) $result['OriginatorConversationID'],
            resultCode: (int) $result['ResultCode'],
            resultDesc: (string) ($result['ResultDesc'] ?? ''),
            receipt: $params['TransactionReceipt'] ?? ($result['TransactionID'] ?? null),
            amount: isset($params['TransactionAmount']) ? (int) round((float) $params['TransactionAmount']) : null,
            raw: $payload,
        );
    }
}
