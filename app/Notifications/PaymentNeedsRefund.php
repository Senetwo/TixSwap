<?php

namespace App\Notifications;

use App\Models\Order;

class PaymentNeedsRefund extends TixSwapNotification
{
    public function __construct(public Order $order) {}

    public function title(): string { return 'We\'re refunding your payment'; }

    public function body(): string
    {
        return 'We received your KES '.number_format($this->order->amount).' for '.$this->order->ticket->event->name
            .', but the ticket was no longer available. We\'ll refund you in full to your M-Pesa number.';
    }

    public function url(): string { return route('home'); }

    public function icon(): string { return 'banknotes'; }

    public function actionText(): string { return 'Browse other tickets'; }

    public function sms(): ?string
    {
        return 'TixSwap: Sorry, the ticket for '.$this->order->ticket->event->name.' sold out before your payment arrived. You will be refunded KES '.number_format($this->order->amount).'.';
    }
}
