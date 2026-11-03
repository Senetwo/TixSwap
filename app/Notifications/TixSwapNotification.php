<?php

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Base for TixSwap notifications. Each one is shown in the app (bell),
 * emailed, and optionally sent by SMS when toSms() returns text.
 */
abstract class TixSwapNotification extends Notification
{
    abstract public function title(): string;

    abstract public function body(): string;

    abstract public function url(): string;

    /** Heroicon name shown next to the notification in the app. */
    public function icon(): string
    {
        return 'info';
    }

    public function actionText(): string
    {
        return 'View details';
    }

    /** Return text to also send an SMS. Keep it under 160 characters. */
    public function sms(): ?string
    {
        return null;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email && config('services.notifications.mail', true)) {
            $channels[] = 'mail';
        }

        if ($this->sms() !== null && config('services.sms.driver') && $notifiable->routeNotificationFor('sms', $this)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->greeting('Hi '.Str::of($notifiable->name)->explode(' ')->first().',')
            ->line($this->body())
            ->action($this->actionText(), $this->url())
            ->salutation('The TixSwap team');
    }

    public function toSms(object $notifiable): ?string
    {
        return $this->sms();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'icon' => $this->icon(),
        ];
    }
}
