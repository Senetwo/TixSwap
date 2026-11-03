<?php

namespace App\Notifications;

use App\Models\Dispute;

class DisputeResolved extends TixSwapNotification
{
    public function __construct(public Dispute $dispute, public bool $forSeller = false) {}

    private function refunded(): bool
    {
        return $this->dispute->status === Dispute::STATUS_REFUNDED;
    }

    public function title(): string
    {
        return 'Update on your reported problem';
    }

    public function body(): string
    {
        $event = $this->dispute->order->ticket->event->name;
        $note = $this->dispute->resolution_note ? ' Note from our team: '.$this->dispute->resolution_note : '';

        if ($this->forSeller) {
            return ($this->refunded()
                ? 'After review, the buyer of your '.$event.' ticket will be refunded, so this sale won\'t be paid out.'
                : 'After review, your payment for '.$event.' has been released. It\'s on its way to your M-Pesa.').$note;
        }

        return ($this->refunded()
            ? 'We\'ve decided in your favour: you\'ll be refunded KES '.number_format($this->dispute->order->amount).' for '.$event.'.'
            : 'After review, we released the payment for your '.$event.' ticket to the seller.').$note;
    }

    public function url(): string
    {
        return $this->forSeller
            ? route('seller.dashboard', ['tab' => 'sold'])
            : route('tickets.purchased.show', $this->dispute->order->ticket_id);
    }

    public function icon(): string { return 'scale'; }

    public function sms(): ?string
    {
        return 'TixSwap: Your reported problem for '.$this->dispute->order->ticket->event->name.' has been reviewed. '
            .($this->forSeller ? 'Details in your dashboard.' : ($this->refunded() ? 'You will be refunded.' : 'See your ticket for details.'));
    }
}
