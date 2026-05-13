<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isClient()) {
            abort(403, 'Access denied.');
        }
        if (auth()->user()->status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been suspended. Please contact admin.');
        }
        return $next($request);
    }
}
