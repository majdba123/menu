<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Check if the request expects JSON
        if ($request->expectsJson()) {
            // Return a JSON response indicating that the user is unauthenticated
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        // For non-JSON requests, redirect to the login route
        return route('login');
    }
}
