<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to bfp_admin only (Personnel Accounts, Announcements
 * management, Analytics & Reports). Assumes EnsureBfpStaff already ran.
 */
class EnsureBfpAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isBfpAdmin(), 403);

        return $next($request);
    }
}
