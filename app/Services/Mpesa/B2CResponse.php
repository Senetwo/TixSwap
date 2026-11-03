<?php

namespace App\Services\Mpesa;

final class B2CResponse
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $originatorConversationId,
        // The fake driver settles instantly; Daraja reports later via callback.
        public readonly ?B2CResult $immediateResult = null,
    ) {}
}
