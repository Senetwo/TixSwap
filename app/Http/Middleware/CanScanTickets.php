<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanScanTickets
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canScanTickets(), 403, 'Gate staff only.');

        return $next($request);
    }
}
