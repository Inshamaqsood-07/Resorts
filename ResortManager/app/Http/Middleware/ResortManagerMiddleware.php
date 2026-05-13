<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResortManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }
        if (auth()->user()->status !== 'approved') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account is pending approval or has been suspended.');
        }
        return $next($request);
    }
}
