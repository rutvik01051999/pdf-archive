<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Redirect;

class CustomThrottleMiddleware extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        try {
            return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
        } catch (ThrottleRequestsException $e) {
            // Custom response for throttled requests
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            $message = "Too many login attempts. Please try again after {$retryAfter} seconds.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 429, $e->getHeaders());
            }

            return Redirect::back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => $message])
                ->withHeaders($e->getHeaders());
        }
    }
}
