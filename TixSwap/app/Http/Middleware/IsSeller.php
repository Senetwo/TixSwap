<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSeller
{
    /**
     * Only allow sellers (and admins) through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && ($user->isSeller() || $user->isAdmin()), 403, 'Sellers only.');

        return $next($request);
    }
}
