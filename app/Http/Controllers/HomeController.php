<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the public homepage with available tickets.
     */
    public function index()
    {
        $tickets = Ticket::where('status', 'approved')->with('event')->latest()->get();
        return view('welcome', ['tickets' => $tickets]);
    }
}