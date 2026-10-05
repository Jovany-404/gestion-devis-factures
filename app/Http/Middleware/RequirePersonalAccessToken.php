<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RequirePersonalAccessToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->currentAccessToken() instanceof PersonalAccessToken) {
            return response()->json(['message' => 'Un jeton API personnel est requis.'], 401);
        }

        return $next($request);
    }
}
