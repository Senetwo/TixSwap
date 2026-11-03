<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisputeController extends Controller
{
    public function index(): View
    {
        $with = ['buyer', 'order.ticket.event', 'order.ticket.seller', 'order.payment', 'resolver'];

        return view('admin.disputes.index', [
            'open' => Dispute::with($with)->where('status', Dispute::STATUS_OPEN)->oldest()->get(),
            'resolved' => Dispute::with($with)->where('status', '!=', Dispute::STATUS_OPEN)->latest('resolved_at')->limit(20)->get(),
        ]);
    }

    public function resolve(Request $request, Dispute $dispute, DisputeService $disputes): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:refund,release',
            'note' => 'required|string|max:2000',
        ]);

        try {
            $disputes->resolve($request->user(), $dispute, $validated['decision'], $validated['note']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $validated['decision'] === 'refund'
            ? 'Decided for the buyer. Send the refund, then record it on the Payouts page.'
            : 'Decided for the seller. Their payout is now on the Payouts page.');
    }

    public function evidence(Dispute $dispute): StreamedResponse
    {
        abort_unless($dispute->evidence_path && Storage::disk('local')->exists($dispute->evidence_path), 404);

        return Storage::disk('local')->response($dispute->evidence_path);
    }
}
