<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileTokenIsValid
{
    /**
     * Handle an incoming request from the mobile application.
     * Validates bearer token against the auth_token table matching firesight-api's requireAuth() middleware.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Authentication required. Please sign in again.'], 401);
        }

        $authToken = AuthToken::with('user')
            ->where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $authToken || ! $authToken->user) {
            return response()->json(['message' => 'Your session has expired. Please sign in again.'], 401);
        }

        $user = $authToken->user;

        if ($user->status === User::STATUS_INACTIVE) {
            return response()->json(['message' => $user->suspensionMessage()], 403);
        }

        $request->setUserResolver(fn () => $user);
        Auth::setUser($user);

        return $next($request);
    }
}
