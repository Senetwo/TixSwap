<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Where people land after logging in: each role's home.
 */
class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return match (true) {
            $request->user()->isAdmin() => redirect()->route('admin.dashboard'),
            $request->user()->isSeller() => redirect()->route('seller.dashboard'),
            $request->user()->isScanner() => redirect()->route('scan.index'),
            default => redirect()->route('home', $request->only('search')),
        };
    }
}
