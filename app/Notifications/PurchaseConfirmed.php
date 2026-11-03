<?php

namespace App\Notifications;

use App\Models\Order;

class PurchaseConfirmed extends TixSwapNotification
{
    public function __construct(public Order $order) {}

    public function title(): string { return 'Your ticket for '.$this->order->ticket->event->name; }

    public function body(): string
    {
        $receipt = $this->order->payment?->transaction_id;

        return 'Payment of KES '.number_format($this->order->amount).' received'.($receipt ? ' (M-Pesa '.$receipt.')' : '')
            .'. Your ticket and QR code are ready in My tickets. Show the QR code at the entrance.';
    }

    public function url(): string { return route('tickets.purchased.show', $this->order->ticket_id); }

    public function icon(): string { return 'ticket'; }

    public function actionText(): string { return 'View your ticket'; }

    public function sms(): ?string
    {
        return 'TixSwap: Payment received. Your ticket for '.$this->order->ticket->event->name.' is in My tickets: '.$this->url();
    }
}
