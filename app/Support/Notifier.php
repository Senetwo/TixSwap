<?php

namespace App\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sends notifications without ever breaking the caller: a mail server
 * or SMS outage must not fail a payment callback or an admin action.
 * Inside a transaction, sending waits until it has committed.
 */
class Notifier
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model|iterable|null  $notifiables
     */
    public static function send(mixed $notifiables, Notification $notification): void
    {
        $send = function () use ($notifiables, $notification) {
            foreach (is_iterable($notifiables) ? $notifiables : [$notifiables] as $notifiable) {
                if (! $notifiable) {
                    continue;
                }

                try {
                    $notifiable->notify($notification);
                } catch (\Throwable $e) {
                    Log::error('Notification failed', [
                        'notification' => $notification::class,
                        'notifiable' => $notifiable::class.'#'.$notifiable->getKey(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        };

        DB::transactionLevel() > 0 ? DB::afterCommit($send) : $send();
    }
}
