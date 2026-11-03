<?php

namespace App\Notifications;

use App\Models\Dispute;

class DisputeOpened extends TixSwapNotification
{
    public function __construct(public Dispute $dispute, public bool $forAdmin = false) {}

    public function title(): string
    {
        return $this->forAdmin ? 'New dispute to review' : 'A buyer reported a problem';
    }

    public function body(): string
    {
        $event = $this->dispute->order->ticket->event->name;

        return $this->forAdmin
            ? 'Order #'.$this->dispute->order_id.' ('.$event.'): '.$this->dispute->reasonLabel().'.'
            : 'The buyer of your ticket for '.$event.' reported: "'.$this->dispute->reasonLabel().'". Your payment is on hold while we review it. We may contact you.';
    }

    public function url(): string
    {
        return $this->forAdmin ? route('admin.disputes.index') : route('seller.dashboard', ['tab' => 'sold']);
    }

    public function icon(): string { return 'scale'; }
}
