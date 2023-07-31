<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        // Define allowed origins here
        $allowedOrigins = [
            'http://34.207.129.27',
        ];

        $origin = $request->header('Origin');

        if (in_array($origin, $allowedOrigins)) {
            // Allow the origin if it's in the list
            $response = $next($request);
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            // Handle invalid origin, like sending a 403 Forbidden response
            return response()->json(['error' => 'Invalid origin'], 403);
        }

        // Set other CORS headers
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
