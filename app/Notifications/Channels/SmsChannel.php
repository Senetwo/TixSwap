<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * SMS via Africa's Talking (https://africastalking.com).
 * SMS_DRIVER=log writes messages to the log instead (the default), so
 * nothing is sent until real credentials are configured.
 */
class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $to = $notifiable->routeNotificationFor('sms', $notification);
        $message = method_exists($notification, 'toSms') ? $notification->toSms($notifiable) : null;

        if (! $to || ! $message) {
            return;
        }

        $config = config('services.sms');

        if (($config['driver'] ?? 'log') !== 'africastalking') {
            Log::info('SMS (not sent: SMS_DRIVER=log)', ['to' => $to, 'message' => $message]);

            return;
        }

        $base = ($config['username'] ?? '') === 'sandbox'
            ? 'https://api.sandbox.africastalking.com'
            : 'https://api.africastalking.com';

        $response = Http::asForm()
            ->acceptJson()
            ->withHeaders(['apiKey' => $config['api_key']])
            ->timeout(15)
            ->post($base.'/version1/messaging', array_filter([
                'username' => $config['username'],
                'to' => '+'.ltrim($to, '+'),
                'message' => $message,
                'from' => $config['sender_id'] ?: null,
            ]));

        if ($response->failed()) {
            throw new RuntimeException('SMS failed ('.$response->status().'): '.$response->body());
        }
    }
}
