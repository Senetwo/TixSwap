<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Sellers may edit their own listings until they are sold, and not
     * while a buyer is in the middle of paying for them.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id && $this->isChangeable($ticket);
    }

    /**
     * Sellers may remove their own listings; admins may remove any.
     * Sold or mid-payment tickets can't be deleted by anyone, because
     * deleting a ticket would also delete its order and payment records.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        if (! $this->isChangeable($ticket)) {
            return false;
        }

        return $user->isAdmin() || $user->id === $ticket->user_id;
    }

    /**
     * Anyone except the seller may buy an approved, unsold ticket.
     */
    public function purchase(User $user, Ticket $ticket): bool
    {
        return $ticket->isAvailable() && $user->id !== $ticket->user_id;
    }

    /**
     * Only the buyer can see the purchased ticket (and its QR code).
     */
    public function viewPurchased(User $user, Ticket $ticket): bool
    {
        return $ticket->buyer_id !== null && $user->id === $ticket->buyer_id;
    }

    /**
     * Proof of purchase is private: only the seller and admins can see it.
     */
    public function viewProof(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->id === $ticket->user_id;
    }

    private function isChangeable(Ticket $ticket): bool
    {
        return ! $ticket->isSold() && ! $ticket->isReserved();
    }
}
