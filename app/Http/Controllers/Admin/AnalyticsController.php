<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    private const RANGES = [30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'];

    public function index(Request $request): View
    {
        $days = array_key_exists((int) $request->query('days'), self::RANGES) ? (int) $request->query('days') : 90;
        $from = now()->subDays($days)->startOfDay();

        $paid = Order::whereNotNull('paid_at')->where('paid_at', '>=', $from)
            ->whereNotIn('status', [Order::STATUS_REFUNDED, Order::STATUS_NEEDS_REFUND]);
        $paidCount = (clone $paid)->count();
        $started = Order::where('created_at', '>=', $from)->count();

        $outcomes = Order::where('created_at', '>=', $from)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $listings = Ticket::where('created_at', '>=', $from)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $disputes = Dispute::where('created_at', '>=', $from)->count();
        $refunded = Order::where('paid_at', '>=', $from)->whereIn('status', [Order::STATUS_REFUNDED, Order::STATUS_NEEDS_REFUND])->count();

        return view('admin.analytics.index', [
            'days' => $days,
            'ranges' => self::RANGES,
            'kpis' => [
                'sales' => (clone $paid)->sum('amount'),
                'revenue' => (clone $paid)->sum('commission'),
                'orders' => $paidCount,
                'aov' => $paidCount ? (clone $paid)->sum('amount') / $paidCount : 0,
                'conversion' => $started ? round($paidCount / $started * 100) : null,
                'started' => $started,
                'reviewHours' => $this->medianReviewHours($from),
                'disputeRate' => $paidCount ? round($disputes / $paidCount * 100, 1) : null,
                'disputes' => $disputes,
                'refunded' => $refunded,
            ],
            'outcomes' => $outcomes,
            'listings' => $listings,
            'weeks' => $this->weeklySales(),
            'topEvents' => $this->topEvents($from),
        ]);
    }

    /**
     * Paid sales per week for the last 12 weeks (oldest first), grouped in PHP so it works on any database.
     *
     * @return Collection<int, array{start: Carbon, total: float, orders: int}>
     */
    private function weeklySales(): Collection
    {
        $start = now()->startOfWeek()->subWeeks(11);
        $orders = Order::whereNotNull('paid_at')->where('paid_at', '>=', $start)
            ->whereNotIn('status', [Order::STATUS_REFUNDED, Order::STATUS_NEEDS_REFUND])
            ->get(['paid_at', 'amount']);

        return collect(range(0, 11))->map(function (int $i) use ($start, $orders) {
            $weekStart = $start->copy()->addWeeks($i);
            $inWeek = $orders->filter(fn ($o) => $o->paid_at->between($weekStart, $weekStart->copy()->endOfWeek()));

            return ['start' => $weekStart, 'total' => (float) $inWeek->sum('amount'), 'orders' => $inWeek->count()];
        });
    }

    /**
     * Median hours from a listing being submitted to an admin approving or rejecting it.
     */
    private function medianReviewHours(Carbon $from): ?float
    {
        $hours = AuditLog::whereIn('action', ['listing.approved', 'listing.rejected'])
            ->where('subject_type', (new Ticket)->getMorphClass())
            ->where('created_at', '>=', $from)
            ->get(['subject_id', 'created_at'])
            ->map(function (AuditLog $log) {
                $submitted = Ticket::whereKey($log->subject_id)->value('created_at');

                return $submitted ? Carbon::parse($submitted)->diffInMinutes($log->created_at) / 60 : null;
            })
            ->filter(fn ($h) => $h !== null)
            ->sort()
            ->values();

        return $hours->isEmpty() ? null : round($hours->median(), 1);
    }

    private function topEvents(Carbon $from): Collection
    {
        return Order::whereNotNull('paid_at')->where('paid_at', '>=', $from)
            ->whereNotIn('status', [Order::STATUS_REFUNDED, Order::STATUS_NEEDS_REFUND])
            ->with('ticket.event')
            ->get()
            ->groupBy(fn (Order $o) => $o->ticket?->event_id)
            ->map(fn (Collection $orders) => [
                'event' => $orders->first()->ticket?->event,
                'tickets' => $orders->count(),
                'sales' => $orders->sum('amount'),
            ])
            ->sortByDesc('sales')
            ->take(8)
            ->values();
    }
}
