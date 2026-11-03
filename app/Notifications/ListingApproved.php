<?php

namespace App\Notifications;

use App\Models\Ticket;

class ListingApproved extends TixSwapNotification
{
    public function __construct(public Ticket $ticket) {}

    public function title(): string { return 'Your ticket is live'; }

    public function body(): string
    {
        return 'Your '.$this->ticket->seat_details.' ticket for '.$this->ticket->event->name.' passed our checks and is now on sale.';
    }

    public function url(): string { return route('tickets.show', $this->ticket); }

    public function icon(): string { return 'check-circle'; }

    public function actionText(): string { return 'See your listing'; }
}
