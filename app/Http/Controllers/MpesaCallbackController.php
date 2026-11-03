<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Services\Mpesa\StkResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives Safaricom's STK push result.
 *
 * Daraja does not sign callbacks, so we protect this endpoint with a
 * secret URL segment, only act on CheckoutRequestIDs we created, check
 * the amount, and process each payment exactly once.
 */
class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, string $secret, CheckoutService $checkout): JsonResponse
    {
        $expected = (string) config('services.mpesa.callback_secret');

        if ($expected === '' || ! hash_equals($expected, $secret)) {
            abort(404);
        }

        $result = StkResult::fromCallback($request->json()->all());

        if (! $result) {
            Log::warning('Malformed M-Pesa callback', ['body' => $request->getContent()]);
        } else {
            $checkout->complete($result);
        }

        // Always acknowledge, otherwise Safaricom keeps retrying.
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * B2C payout result / queue timeout.
     */
    public function b2c(Request $request, string $secret, string $type, \App\Services\PayoutService $payouts): JsonResponse
    {
        $expected = (string) config('services.mpesa.callback_secret');

        if ($expected === '' || ! hash_equals($expected, $secret)) {
            abort(404);
        }

        if ($type === 'b2c-timeout') {
            $id = $request->input('Result.OriginatorConversationID') ?? $request->input('OriginatorConversationID');
            if ($id) {
                $payouts->markTimedOut((string) $id);
            }
        } elseif ($result = \App\Services\Mpesa\B2CResult::fromCallback($request->json()->all())) {
            $payouts->applyResult($result);
        } else {
            Log::warning('Malformed B2C callback', ['body' => $request->getContent()]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
