<?php

namespace App\Notifications;

use App\Models\Ticket;

class ListingRejected extends TixSwapNotification
{
    public function __construct(public Ticket $ticket) {}

    public function title(): string { return 'Your listing needs changes'; }

    public function body(): string
    {
        return 'We couldn\'t approve your ticket for '.$this->ticket->event->name.'. Reason: '.$this->ticket->admin_notes;
    }

    public function url(): string { return route('seller.dashboard', ['tab' => 'rejected']); }

    public function icon(): string { return 'warning'; }

    public function actionText(): string { return 'View listing'; }
}
