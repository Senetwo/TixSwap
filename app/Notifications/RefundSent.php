<?php

namespace App\Notifications;

use App\Models\Order;

class RefundSent extends TixSwapNotification
{
    public function __construct(public Order $order, public string $reference) {}

    public function title(): string { return 'Refund sent: KES '.number_format($this->order->amount); }

    public function body(): string
    {
        return 'We\'ve refunded KES '.number_format($this->order->amount).' for '.$this->order->ticket->event->name
            .' to your M-Pesa (receipt '.$this->reference.').';
    }

    public function url(): string { return route('cart.index'); }

    public function icon(): string { return 'banknotes'; }

    public function sms(): ?string
    {
        return 'TixSwap: KES '.number_format($this->order->amount).' refunded to your M-Pesa for '.$this->order->ticket->event->name.'. Receipt '.$this->reference.'.';
    }
}
