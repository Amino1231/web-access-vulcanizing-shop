<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Not logged in → 404
        if (! Auth::check()) {
            abort(404);
        }

        // Logged in but not an employee → 404
        if (! Auth::user()->hasRole('employee')) {
            abort(404);
        }

        return $next($request);
    }
}