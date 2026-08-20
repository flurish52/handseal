<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferralCode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('ref') && ! $request->session()->has('referral_code')) {
            $request->session()->put('referral_code', $request->string('ref')->toString());
        }

        return $next($request);
    }
}
