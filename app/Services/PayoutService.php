<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Payout;
use App\Notifications\PayoutSent;
use App\Services\Mpesa\B2CResult;
use App\Services\Mpesa\MpesaException;
use App\Services\Mpesa\MpesaGateway;
use App\Support\Notifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Automatic seller payouts through M-Pesa B2C.
 *
 * Paying twice is the main risk, so:
 *  - a payout can only be sent while "pending" or after a confirmed failure
 *  - it moves to "processing" (row-locked) before the request goes out
 *  - a timeout leaves it "processing": the money may or may not have
 *    moved, so an admin checks the M-Pesa statement before retrying
 *  - results are applied once, matched on our own OriginatorConversationID
 */
class PayoutService
{
    public function __construct(private readonly MpesaGateway $mpesa) {}

    public static function enabled(): bool
    {
        return (bool) config('services.mpesa.b2c_enabled');
    }

    /**
     * @throws RuntimeException when the payout can't be sent
     */
    public function send(Payout $payout): Payout
    {
        if (! self::enabled()) {
            throw new RuntimeException('M-Pesa payouts are switched off (MPESA_B2C_ENABLED).');
        }

        $payout = DB::transaction(function () use ($payout) {
            $payout = Payout::with('seller', 'order.ticket.event')->whereKey($payout->id)->lockForUpdate()->firstOrFail();

            if (! in_array($payout->status, [Payout::STATUS_PENDING, Payout::STATUS_FAILED], true)) {
                throw new RuntimeException('This payout has already been sent.');
            }

            if (! $payout->seller?->payout_phone) {
                throw new RuntimeException('The seller hasn\'t added a payout M-Pesa number.');
            }

            $payout->forceFill([
                'status' => Payout::STATUS_PROCESSING,
                'method' => 'mpesa_b2c',
                'originator_conversation_id' => 'TIXP-'.$payout->id.'-'.Str::lower(Str::random(10)),
                'failure_reason' => null,
                'attempts' => $payout->attempts + 1,
            ])->save();

            return $payout;
        });

        try {
            $response = $this->mpesa->b2cPayment(
                msisdn: $payout->seller->payout_phone,
                amount: (int) floor((float) $payout->amount),
                originatorConversationId: $payout->originator_conversation_id,
                remarks: 'TixSwap payout, order '.$payout->order_id,
                resultUrl: $this->callbackUrl('b2c-result'),
                timeoutUrl: $this->callbackUrl('b2c-timeout'),
            );
        } catch (MpesaException $e) {
            // The request never reached M-Pesa (or was refused): safe to retry.
            $payout->forceFill(['status' => Payout::STATUS_FAILED, 'failure_reason' => Str::limit($e->getMessage(), 250)])->save();
            Log::warning('B2C payout request failed', ['payout_id' => $payout->id, 'error' => $e->getMessage()]);

            throw new RuntimeException('M-Pesa didn\'t accept the payout: '.$e->getMessage(), previous: $e);
        }

        $payout->forceFill(['conversation_id' => $response->conversationId])->save();
        AuditLog::record('payout.sent_mpesa', $payout, ['amount' => $payout->amount, 'to' => $payout->seller->payout_phone]);

        if ($response->immediateResult) {
            $this->applyResult($response->immediateResult);
        }

        return $payout->refresh();
    }

    /**
     * Send every pending payout that has a phone number. Returns [sent, failed].
     *
     * @return array{0: int, 1: int}
     */
    public function sendAllPending(): array
    {
        $sent = $failed = 0;

        Payout::where('status', Payout::STATUS_PENDING)
            ->whereHas('seller', fn ($q) => $q->whereNotNull('payout_phone'))
            ->pluck('id')
            ->each(function (int $id) use (&$sent, &$failed) {
                try {
                    $this->send(Payout::find($id));
                    $sent++;
                } catch (RuntimeException) {
                    $failed++;
                }
            });

        return [$sent, $failed];
    }

    /**
     * Apply Safaricom's B2C result (callback, or immediate for the fake driver).
     */
    public function applyResult(B2CResult $result): ?Payout
    {
        $payout = DB::transaction(function () use ($result) {
            $payout = Payout::where('originator_conversation_id', $result->originatorConversationId)->lockForUpdate()->first();

            if (! $payout) {
                Log::warning('B2C result for unknown payout', ['id' => $result->originatorConversationId]);

                return null;
            }

            if ($payout->status !== Payout::STATUS_PROCESSING) {
                return $payout; // already settled
            }

            if ($result->successful()) {
                $payout->forceFill([
                    'status' => Payout::STATUS_PAID,
                    'reference' => $result->receipt,
                    'paid_at' => now(),
                    'raw_result' => $result->raw ?: null,
                ])->save();
                Notifier::send($payout->seller, new PayoutSent($payout->load('order.ticket.event')));
            } else {
                $payout->forceFill([
                    'status' => Payout::STATUS_FAILED,
                    'failure_reason' => Str::limit($result->resultDesc, 250),
                    'raw_result' => $result->raw ?: null,
                ])->save();
            }

            return $payout;
        });

        return $payout;
    }

    /**
     * M-Pesa couldn't process the request in time. The money may or may
     * not have moved, so we only flag it for an admin to check.
     */
    public function markTimedOut(string $originatorConversationId): void
    {
        Payout::where('originator_conversation_id', $originatorConversationId)
            ->where('status', Payout::STATUS_PROCESSING)
            ->update(['failure_reason' => 'M-Pesa timed out. Check the M-Pesa statement before retrying.']);
    }

    /**
     * Admin confirmed (e.g. from the M-Pesa statement) that a stuck
     * payout did not go through, so it can be sent again.
     */
    public function confirmFailed(Payout $payout): void
    {
        DB::transaction(function () use ($payout) {
            $payout = Payout::whereKey($payout->id)->lockForUpdate()->firstOrFail();

            if ($payout->status !== Payout::STATUS_PROCESSING) {
                throw new RuntimeException('Only payouts waiting on M-Pesa can be marked as failed.');
            }

            $payout->forceFill(['status' => Payout::STATUS_FAILED, 'failure_reason' => 'Confirmed not received; ready to retry.'])->save();
            AuditLog::record('payout.marked_failed', $payout);
        });
    }

    private function callbackUrl(string $type): string
    {
        $secret = config('services.mpesa.callback_secret');

        if (! $secret && config('services.mpesa.driver') === 'daraja') {
            throw new RuntimeException('Set MPESA_CALLBACK_SECRET in .env before sending payouts.');
        }

        $base = config('services.mpesa.callback_base_url') ?: config('app.url');

        return rtrim($base, '/').'/mpesa/callback/'.($secret ?: 'not-configured').'/'.$type;
    }
}
