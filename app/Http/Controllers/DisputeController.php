<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Order;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Buyers reporting a problem with a ticket they paid for.
 */
class DisputeController extends Controller
{
    public function create(Order $order): View|RedirectResponse
    {
        $this->authorize('view', $order);

        if (! $order->canBeDisputed()) {
            return redirect()->route('tickets.purchased.show', $order->ticket_id)
                ->with('error', 'This order can no longer be disputed.');
        }

        return view('disputes.create', [
            'order' => $order->load('ticket.event'),
            'reasons' => Dispute::REASONS,
        ]);
    }

    public function store(Request $request, Order $order, DisputeService $disputes): RedirectResponse
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(Dispute::REASONS))],
            'details' => 'required|string|min:20|max:2000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'details.min' => 'Please describe what happened in a bit more detail (at least 20 characters).',
        ]);

        try {
            $disputes->open($request->user(), $order, $validated['reason'], $validated['details'], $request->file('evidence'));
        } catch (\RuntimeException) {
            return redirect()->route('tickets.purchased.show', $order->ticket_id)
                ->with('error', 'This order can no longer be disputed.');
        }

        return redirect()->route('tickets.purchased.show', $order->ticket_id)
            ->with('success', "Thanks, we've received your report. The seller's payment is on hold while our team reviews it.");
    }
}
