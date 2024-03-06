<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Allow all origins (not recommended for production)
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Set rate limiting headers
        // $response->headers->set('X-RateLimit-Limit', $request->header('X-RateLimit-Limit'));
        // $response->headers->set('X-RateLimit-Remaining', $request->header('X-RateLimit-Remaining'));
        // $response->headers->set('X-RateLimit-Reset', $request->header('X-RateLimit-Reset'));
        return $response;
    }
}
