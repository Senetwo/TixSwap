<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Public marketplace pages: no login needed to browse.
 */
class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $events = Event::onSale()
            ->search($request->query('search'))
            ->withCount('availableTickets')
            ->withMin('availableTickets', 'price')
            ->orderBy('date')
            ->paginate(24)
            ->withQueryString();

        return view('home', ['events' => $events]);
    }

    public function event(Event $event): View
    {
        return view('events.show', [
            'event' => $event,
            'tickets' => $event->availableTickets()->with('seller')->orderBy('price')->get(),
        ]);
    }

    /**
     * Upcoming events with tickets on sale, for search engines.
     */
    public function sitemap(): Response
    {
        $events = Event::onSale()->orderBy('date')->get(['id', 'name', 'updated_at']);

        return response()
            ->view('sitemap', ['events' => $events])
            ->header('Content-Type', 'application/xml');
    }
}
