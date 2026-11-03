<?php

namespace App\Services\Mpesa;

interface MpesaGateway
{
    /**
     * Send an STK push (the "Enter M-Pesa PIN" prompt) to the buyer's phone.
     *
     * @param  string  $msisdn  Phone in 2547XXXXXXXX / 2541XXXXXXXX format
     * @param  int  $amount  Whole Kenyan shillings
     *
     * @throws MpesaException when Safaricom can't be reached or rejects the request
     */
    public function stkPush(string $msisdn, int $amount, string $accountReference, string $description, string $callbackUrl): StkPushResponse;

    /**
     * Ask Safaricom for the outcome of an STK push.
     * Returns null while the buyer still hasn't responded.
     */
    public function query(string $checkoutRequestId): ?StkResult;

    /**
     * Send money from the business account to a phone (B2C).
     * The result arrives later on $resultUrl (or immediately for the fake driver).
     *
     * @throws MpesaException when Safaricom can't be reached or rejects the request
     */
    public function b2cPayment(string $msisdn, int $amount, string $originatorConversationId, string $remarks, string $resultUrl, string $timeoutUrl): B2CResponse;
}
