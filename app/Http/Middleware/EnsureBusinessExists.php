<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessExists
{
    /**
     * Redirects a signed-in user with no business yet to the onboarding screen.
     * Google sign-in itself is never gated behind this — only the app screens are.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            Auth::check()
            && ! Auth::user()->businesses()->exists()
            && ! $request->routeIs('business.create')
            && ! $request->routeIs('business.store')
        ) {
            return redirect()->route('business.create');
        }

        return $next($request);
    }
}
