<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the entire BFP web portal to active bfp_admin accounts.
 * Personnel and residents authenticate through the mobile app, not this portal.
 */
class EnsureBfpAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isBfpAdmin(), 403);

        return $next($request);
    }
}
