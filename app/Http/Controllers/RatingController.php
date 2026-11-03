<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Buyers rate the seller once the event is over.
 */
class RatingController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'positive' => 'required|boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        abort_unless(self::canRate($order), 403, 'This purchase can\'t be rated.');

        $rating = new SellerRating;
        $rating->forceFill([
            'order_id' => $order->id,
            'buyer_id' => $order->user_id,
            'seller_id' => $order->ticket->user_id,
            'positive' => (bool) $validated['positive'],
            'comment' => $validated['comment'] ?? null,
        ])->save();

        return back()->with('success', 'Thanks for rating the seller. It helps other fans buy safely.');
    }

    /**
     * Paid, the event has happened, not refunded, and not rated yet.
     */
    public static function canRate(Order $order): bool
    {
        return $order->paid_at !== null
            && in_array($order->status, [Order::STATUS_PAID], true)
            && $order->ticket?->event?->date?->isPast()
            && ! SellerRating::where('order_id', $order->id)->exists();
    }
}
