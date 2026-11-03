<?php

namespace App\Services\Mpesa;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Safaricom Daraja API (Lipa na M-Pesa Online / STK Push).
 */
class DarajaGateway implements MpesaGateway
{
    public function __construct(private readonly array $config)
    {
        foreach (['consumer_key', 'consumer_secret', 'shortcode', 'passkey'] as $key) {
            if (empty($config[$key])) {
                throw new MpesaException("M-Pesa is not configured: set MPESA_".strtoupper($key).' in .env');
            }
        }
    }

    public function stkPush(string $msisdn, int $amount, string $accountReference, string $description, string $callbackUrl): StkPushResponse
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');

        $response = $this->send('/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $this->config['shortcode'],
            'Password' => $this->password($timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => $this->config['transaction_type'] ?? 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $msisdn,
            'PartyB' => $this->config['party_b'] ?: $this->config['shortcode'],
            'PhoneNumber' => $msisdn,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => mb_substr($accountReference, 0, 12),
            'TransactionDesc' => mb_substr($description, 0, 13),
        ]);

        if (($response['ResponseCode'] ?? null) !== '0' || empty($response['CheckoutRequestID'])) {
            throw new MpesaException('M-Pesa rejected the payment request: '.($response['errorMessage'] ?? $response['ResponseDescription'] ?? 'unknown error'));
        }

        return new StkPushResponse(
            checkoutRequestId: $response['CheckoutRequestID'],
            merchantRequestId: $response['MerchantRequestID'] ?? '',
            customerMessage: $response['CustomerMessage'] ?? 'Check your phone to complete the payment.',
        );
    }

    public function query(string $checkoutRequestId): ?StkResult
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');

        try {
            $response = $this->send('/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $this->config['shortcode'],
                'Password' => $this->password($timestamp),
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);
        } catch (MpesaException) {
            // Daraja answers with an error while the buyer hasn't responded yet.
            return null;
        }

        if (! isset($response['ResultCode'])) {
            return null;
        }

        return new StkResult(
            checkoutRequestId: $checkoutRequestId,
            resultCode: (int) $response['ResultCode'],
            resultDesc: (string) ($response['ResultDesc'] ?? ''),
            raw: $response,
        );
    }

    private function send(string $path, array $payload): array
    {
        try {
            $response = $this->http()->withToken($this->accessToken())->post($path, $payload);
        } catch (ConnectionException $e) {
            throw new MpesaException('Could not reach M-Pesa: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Daraja request failed', ['path' => $path, 'status' => $response->status(), 'body' => $response->json()]);

            throw new MpesaException('M-Pesa request failed ('.$response->status().'): '.($response->json('errorMessage') ?? 'unknown error'));
        }

        return $response->json() ?? [];
    }

    /**
     * OAuth token, cached until shortly before it expires (Daraja: 1 hour).
     */
    private function accessToken(): string
    {
        $cacheKey = 'mpesa.token.'.md5($this->config['consumer_key'].$this->config['environment']);

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        try {
            $response = $this->http()
                ->withBasicAuth($this->config['consumer_key'], $this->config['consumer_secret'])
                ->get('/oauth/v1/generate', ['grant_type' => 'client_credentials']);
        } catch (ConnectionException $e) {
            throw new MpesaException('Could not reach M-Pesa: '.$e->getMessage(), previous: $e);
        }

        $token = $response->json('access_token');

        if ($response->failed() || ! $token) {
            throw new MpesaException('M-Pesa authentication failed. Check MPESA_CONSUMER_KEY and MPESA_CONSUMER_SECRET.');
        }

        Cache::put($cacheKey, $token, max(60, (int) $response->json('expires_in', 3600) - 60));

        return $token;
    }

    private function password(string $timestamp): string
    {
        return base64_encode($this->config['shortcode'].$this->config['passkey'].$timestamp);
    }

    private function http(): PendingRequest
    {
        $baseUrl = ($this->config['environment'] ?? 'sandbox') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        return Http::baseUrl($baseUrl)->acceptJson()->timeout(30)->connectTimeout(10);
    }
}
