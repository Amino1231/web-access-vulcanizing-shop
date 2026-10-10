<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Not logged in → 404 (hide that this route exists)
        if (! Auth::check()) {
            abort(404);
        }

        // Logged in but not a customer → 404
        if (! Auth::user()->hasRole('customer')) {
            abort(404);
        }

        return $next($request);
    }
}