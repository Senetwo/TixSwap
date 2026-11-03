<?php

namespace App\Notifications;

use App\Models\Order;

class TicketSold extends TixSwapNotification
{
    public function __construct(public Order $order) {}

    public function title(): string { return 'You sold a ticket!'; }

    public function body(): string
    {
        return 'Your ticket for '.$this->order->ticket->event->name.' sold for KES '.number_format($this->order->amount)
            .'. You\'ll receive KES '.number_format($this->order->seller_amount).' after the event.';
    }

    public function url(): string { return route('seller.dashboard', ['tab' => 'sold']); }

    public function icon(): string { return 'banknotes'; }

    public function sms(): ?string
    {
        return 'TixSwap: Your ticket for '.$this->order->ticket->event->name.' sold! KES '.number_format($this->order->seller_amount).' will be paid to you after the event.';
    }
}
