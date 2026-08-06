<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the BFP web portal to bfp_personnel and bfp_admin accounts.
 * Residents authenticate through the mobile app, not this portal.
 */
class EnsureBfpStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isBfpStaff(), 403);

        return $next($request);
    }
}
