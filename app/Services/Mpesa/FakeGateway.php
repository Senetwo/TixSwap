<?php

namespace App\Services\Mpesa;

use Illuminate\Support\Str;

/**
 * Development stand-in for Daraja: every STK push "succeeds" and the
 * first status check reports it as paid. Never used in production.
 */
class FakeGateway implements MpesaGateway
{
    public function stkPush(string $msisdn, int $amount, string $accountReference, string $description, string $callbackUrl): StkPushResponse
    {
        return new StkPushResponse(
            checkoutRequestId: 'ws_CO_FAKE_'.Str::upper(Str::random(16)),
            merchantRequestId: 'FAKE-'.Str::upper(Str::random(8)),
            customerMessage: 'Simulated payment (MPESA_DRIVER=fake). No money will move.',
        );
    }

    public function query(string $checkoutRequestId): ?StkResult
    {
        return new StkResult(
            checkoutRequestId: $checkoutRequestId,
            resultCode: 0,
            resultDesc: 'The service request is processed successfully. (simulated)',
        );
    }
}
