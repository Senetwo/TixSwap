<?php

namespace App\Notifications;

use App\Models\Payout;

class PayoutSent extends TixSwapNotification
{
    public function __construct(public Payout $payout) {}

    public function title(): string { return 'Payout sent: KES '.number_format($this->payout->amount); }

    public function body(): string
    {
        return 'We\'ve sent KES '.number_format($this->payout->amount).' to your M-Pesa for your '
            .$this->payout->order->ticket->event->name.' ticket (M-Pesa receipt '.$this->payout->reference.').';
    }

    public function url(): string { return route('seller.dashboard'); }

    public function icon(): string { return 'banknotes'; }

    public function sms(): ?string
    {
        return 'TixSwap: KES '.number_format($this->payout->amount).' sent to your M-Pesa for '.$this->payout->order->ticket->event->name.'. Receipt '.$this->payout->reference.'.';
    }
}
