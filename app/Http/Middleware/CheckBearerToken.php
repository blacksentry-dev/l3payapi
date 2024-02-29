<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exclude routes from Bearer token check
        $excludedRoutes = ['login', 'register', 'verify-email']; // Add more if needed

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Additional token validation here

        return $next($request);
    }
}
